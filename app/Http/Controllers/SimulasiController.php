<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use App\Models\Soal;
use App\Models\MataPelajaran;
use App\Models\Simulasi;
use App\Models\SimulasiSoal;
use App\Models\Nilai;
use App\Services\PenilaianService;
use App\Services\FirestoreExamSessionStore;
use App\Services\FirestoreJawabanPesertaStore;
use App\Services\FirestoreMataPelajaranStore;
use App\Services\FirestoreSimulasiStore;
use App\Services\FirestoreTokenStore;
use App\Services\FirestoreUserStore;
use App\Services\FirestoreRestClient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SimulasiController extends Controller
{
    private function firestoreStudentPrimary(): bool
    {
        return filter_var(env('FIRESTORE_STUDENT_PRIMARY', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param array<string, mixed> $hasil
     */
    private function computeSkorPoin(array $hasil): float
    {
        $detail = $hasil['detail_jawaban'] ?? null;
        if (is_array($detail) && !empty($detail)) {
            $sum = 0.0;
            foreach ($detail as $row) {
                if (is_array($row)) {
                    $sum += (float) ($row['nilai'] ?? 0);
                }
            }
            return $sum;
        }

        return (float) ($hasil['nilai_total'] ?? 0);
    }

    /**
     * @param array<string, mixed> $hasil
     */
    private function computeSkorMaksimal(array $hasil): float
    {
        $detail = $hasil['detail_jawaban'] ?? null;
        if (is_array($detail) && !empty($detail)) {
            $sum = 0.0;
            foreach ($detail as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $maks = $row['maksimal'] ?? null;
                $sum += (float) (($maks === null || $maks === '') ? 1 : $maks);
            }
            return $sum > 0 ? $sum : 1.0;
        }

        $fallback = (float) ($hasil['jumlah_soal'] ?? 0);
        return $fallback > 0 ? $fallback : 1.0;
    }

    /**
     * @param mixed $value
     * @return array<int, mixed>
     */
    private function normalizeDetailJawaban(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            if (is_string($decoded) && $decoded !== '') {
                $decoded2 = json_decode($decoded, true);
                if (is_array($decoded2)) {
                    return $decoded2;
                }
            }
        }

        return [];
    }

    /**
     * Estimate the expected number of questions from DB for the given soal IDs.
     * For paket soal, this counts its sub-soal entries.
     *
     * @param array<int, mixed> $soalIds
     */
    private function expectedJumlahSoalFromDb(array $soalIds): int
    {
        $ids = [];
        foreach ($soalIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            return 0;
        }

        $rows = Soal::query()
            ->whereIn('id', $ids)
            ->withCount('subSoal')
            ->get(['id']);

        $sum = 0;
        foreach ($rows as $soal) {
            $sub = (int) ($soal->sub_soal_count ?? 0);
            $sum += max(1, $sub);
        }

        return $sum;
    }

    private function syncUserToFirestore(User $user): void
    {
        try {
            /** @var FirestoreRestClient $client */
            $client = app(FirestoreRestClient::class);
            $data = $user->getAttributes();
            $id = (int) ($data['id'] ?? 0);
            if ($id > 0) {
                $client->upsertByField('users', 'id', $id, $data);
            }
        } catch (\Throwable $e) {
            // best-effort
        }
    }

    private function syncTokenToFirestore(Token $token): void
    {
        try {
            /** @var FirestoreRestClient $client */
            $client = app(FirestoreRestClient::class);
            $data = $token->getAttributes();
            $id = (int) ($data['id'] ?? 0);
            if ($id > 0) {
                $client->upsertByField('tokens', 'id', $id, $data);
            }
        } catch (\Throwable $e) {
            // best-effort
        }
    }

    public function generateSimulasi()
    {
        // Get all students from database
        $students = User::where('role', 'siswa')
                       ->orderBy('rombongan_belajar')
                       ->orderBy('name')
                       ->get();

        // Get all paket soal (parent records) so admin can pick a specific packet.
        // Each paket contains its questions in sub_soal.
        $paketSoal = Soal::query()
            ->where('jenis_soal', 'paket')
            ->with(['mataPelajaran'])
            ->withCount('subSoal')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($soal) {
                $mapel = $soal->mataPelajaran;
                $namaMapel = $mapel?->nama ?? '-';
                $jumlah = (int) ($soal->sub_soal_count ?? 0);
                return [
                    'id' => $soal->id,
                    'kode' => $soal->kode_soal,
                    'nama' => $namaMapel,
                    'jumlah_soal' => $jumlah,
                    'label' => "{$soal->kode_soal} - {$namaMapel} ({$jumlah} Soal)",
                ];
            });

        return view('simulasi.generate', compact('students', 'paketSoal'));
    }

    public function generatedActive()
    {
        $now = now();

        $simulasis = Simulasi::query()
            ->where('is_active', true)
            ->with([
                'mataPelajaran',
                'creator',
                'simulasiSoal.soal' => function ($q) {
                    $q->withCount('subSoal');
                },
            ])
            ->withCount('simulasiPeserta')
            ->orderByDesc('waktu_mulai')
            ->get();

        return view('simulasi.generated-active', compact('simulasis', 'now'));
    }

    public function storeSimulasi(Request $request)
    {
        $request->validate([
            'paket_soal_id' => 'required|exists:soal,id',
            'nama_simulasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'durasi_menit' => 'required|integer|min:1',
            'peserta' => 'required|array|min:1',
            'peserta.*' => 'exists:users,id',
        ]);

        $paket = Soal::with('mataPelajaran')->findOrFail((int) $request->paket_soal_id);
        if (($paket->jenis_soal ?? null) !== 'paket') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Paket soal tidak valid. Silakan pilih paket soal yang benar.');
        }

        // Rule: prevent generating the same paket if there is still an active simulasi using it.
        $existingActive = Simulasi::query()
            ->where('is_active', true)
            ->whereHas('simulasiSoal', function ($q) use ($paket) {
                $q->where('soal_id', (int) $paket->id);
            })
            ->exists();

        if ($existingActive) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Soal sudah digenerate, jika ingin generate ulang harap hentikan simulasi yang telah dibuat sebelumnya');
        }

        DB::beginTransaction();
        try {
            // Create simulasi
            $simulasi = Simulasi::create([
                'nama_simulasi' => $request->nama_simulasi,
                'deskripsi' => $request->deskripsi,
                'mata_pelajaran_id' => $paket->mata_pelajaran_id,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'durasi_menit' => $request->durasi_menit,
                'created_by' => Auth::id() ?? 1, // Default to admin if not logged in
                'is_active' => true,
            ]);

            // Attach the selected paket only.
            SimulasiSoal::create([
                'simulasi_id' => $simulasi->id,
                'soal_id' => $paket->id,
                'urutan' => 1,
            ]);

            // Add peserta to simulasi_peserta so monitor page only shows registered students.
            if (Schema::hasTable('simulasi_peserta')) {
                $now = now();

                // Only persist students (defensive)
                $studentIds = User::query()
                    ->where('role', 'siswa')
                    ->whereIn('id', $request->peserta)
                    ->pluck('id')
                    ->unique()
                    ->values();

                $rows = $studentIds
                    ->map(fn ($id) => [
                        'simulasi_id' => $simulasi->id,
                        'user_id' => $id,
                        'status' => 'belum_mulai',
                        'waktu_mulai' => null,
                        'waktu_selesai' => null,
                        'nilai' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->all();

                if (!empty($rows)) {
                    DB::table('simulasi_peserta')->insertOrIgnore($rows);

                    // Also sync registered participants to Firestore so monitoring can be fully Firestore-based.
                    if ($this->firestoreStudentPrimary()) {
                        try {
                            /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                            $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);

                            // Fetch SQLite ids (best-effort) then upsert to Firestore.
                            $sqliteRows = DB::table('simulasi_peserta')
                                ->where('simulasi_id', $simulasi->id)
                                ->whereIn('user_id', $studentIds)
                                ->get(['id', 'user_id', 'status', 'waktu_mulai', 'waktu_selesai', 'nilai']);

                            foreach ($sqliteRows as $r) {
                                $sp->upsert((int) $r->user_id, (int) $simulasi->id, [
                                    'id' => (int) $r->id,
                                    'status' => (string) ($r->status ?? 'belum_mulai'),
                                    'waktu_mulai' => $r->waktu_mulai ? \Carbon\Carbon::parse($r->waktu_mulai) : null,
                                    'waktu_selesai' => $r->waktu_selesai ? \Carbon\Carbon::parse($r->waktu_selesai) : null,
                                    'nilai' => $r->nilai !== null ? (float) $r->nilai : null,
                                    'created_at' => $now,
                                ]);
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('soal.index')->with('success', 'Simulasi berhasil di-generate! Soal telah ditandai sebagai aktif.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat simulasi: ' . $e->getMessage())->withInput();
        }
    }

    public function stopSimulasi(Simulasi $simulasi)
    {
        if (!$simulasi->is_active) {
            return redirect()->back()->with('success', 'Simulasi sudah dihentikan.');
        }

        $simulasi->is_active = false;
        $simulasi->save();

        return redirect()->back()->with('success', 'Simulasi berhasil dihentikan.');
    }

    public function generateToken()
    {
        // Get current active and not expired token from database
        $currentToken = Token::where('is_active', true)
                            ->where('expires_at', '>', Carbon::now())
                            ->latest()
                            ->first();
        
        // If no active token or all are expired, create new one (1 hour from now)
        if (!$currentToken) {
            // Deactivate all old tokens first
            Token::where('is_active', true)->update(['is_active' => false]);
            
            $currentToken = Token::create([
                'token' => Token::generateRandomToken(),
                'expires_at' => Carbon::now()->addHour(),
                'is_active' => true,
            ]);
        }
        
        return view('simulasi.token', compact('currentToken'));
    }

    public function refreshToken(Request $request)
    {
        // Deactivate all old tokens
        Token::where('is_active', true)->update(['is_active' => false]);
        
        // Generate new token
        $newToken = Token::create([
            'token' => Token::generateRandomToken(),
            'expires_at' => Carbon::now()->addHour(),
            'is_active' => true,
        ]);
        
        return response()->json([
            'token' => $newToken->token,
            'expires_at' => $newToken->expires_at->format('Y-m-d H:i:s'),
            'message' => 'Token baru berhasil dibuat'
        ]);
    }

    // Student Login Methods
    public function showStudentLogin()
    {
        // Redirect if already logged in as student
        if (Session::has('student_id')) {
            return redirect()->route('simulasi.student.dashboard');
        }
        
        return view('simulasi.login');
    }

    public function studentLogin(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
            'password' => 'required|string',
        ]);

        $student = null;
        $studentPasswordHash = null;

        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var FirestoreUserStore $store */
                $store = app(FirestoreUserStore::class);
                $fsStudent = $store->findStudentByNisn($request->nisn);
                if (is_array($fsStudent)) {
                    $studentPasswordHash = (string) ($fsStudent['password'] ?? '');
                    $student = (object) [
                        'id' => (int) ($fsStudent['id'] ?? 0),
                        'name' => (string) ($fsStudent['name'] ?? ''),
                        'nisn' => (string) ($fsStudent['nisn'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {
                // fallback below
            }
        }

        // Fallback to SQLite during migration
        if (!$student || (int) $student->id <= 0) {
            $dbStudent = User::where('nisn', $request->nisn)
                ->where('role', 'siswa')
                ->first();

            if ($dbStudent) {
                $studentPasswordHash = (string) $dbStudent->password;
                $student = (object) [
                    'id' => (int) $dbStudent->id,
                    'name' => (string) $dbStudent->name,
                    'nisn' => (string) $dbStudent->nisn,
                ];
                if ($this->firestoreStudentPrimary()) {
                    $this->syncUserToFirestore($dbStudent);
                }
            }
        }

        // Check if student exists and password is correct
        if (!$student || $studentPasswordHash === null || !Hash::check($request->password, $studentPasswordHash)) {
            return back()->with('error', 'NISN atau password salah')->withInput();
        }

        // Prevent session data bleed between different student accounts
        // (e.g., exam_answers/exam_data from a previous student filling a new student's exam page).
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Store student ID in session
        Session::put('student_id', $student->id);
        Session::put('student_name', $student->name);
        Session::put('student_nisn', $student->nisn);

        return redirect()->route('simulasi.student.dashboard');
    }

    public function studentDashboard()
    {
        // Check if student is logged in
        if (!Session::has('student_id')) {
            return redirect()->route('simulasi.login')->with('error', 'Silakan login terlebih dahulu');
        }

        $studentId = (int) Session::get('student_id');
        $student = null;

        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var FirestoreUserStore $store */
                $store = app(FirestoreUserStore::class);
                $fsStudent = $store->findById($studentId);
                if (is_array($fsStudent)) {
                    $student = (object) $fsStudent;
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        if (!$student) {
            $student = User::find($studentId);
            if ($student && $this->firestoreStudentPrimary()) {
                $this->syncUserToFirestore($student);
            }
        }

        if (!$student) {
            Session::flush();
            return redirect()->route('simulasi.login')->with('error', 'Session tidak valid');
        }

        $currentToken = null;

        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var FirestoreTokenStore $tokenStore */
                $tokenStore = app(FirestoreTokenStore::class);
                $currentToken = $tokenStore->getCurrentToken();
            } catch (\Throwable $e) {
                $currentToken = null;
            }
        }

        // Fallback to SQLite during migration
        if (!$currentToken) {
            $dbToken = Token::getCurrentToken();
            $currentToken = $dbToken;
            if ($dbToken && $this->firestoreStudentPrimary()) {
                $this->syncTokenToFirestore($dbToken);
            }
        }
        
        // If no active token or expired, create new one
        if (!$currentToken) {
            $dbToken = Token::create([
                'token' => Token::generateRandomToken(),
                'expires_at' => Carbon::now()->addHour(),
                'is_active' => true,
            ]);

            if ($this->firestoreStudentPrimary()) {
                $this->syncTokenToFirestore($dbToken);
            }

            $currentToken = $dbToken;
        }

        return view('simulasi.student-dashboard', compact('student', 'currentToken'));
    }

    public function updateToken(Request $request)
    {
        // This endpoint is called when refresh button is clicked
        // Check if current token in database has changed
        $currentToken = Token::getCurrentToken();
        
        if ($currentToken) {
            return response()->json([
                'success' => true,
                'token' => $currentToken->token,
                'expires_at' => $currentToken->expires_at->format('Y-m-d H:i:s'),
                'message' => 'Token masih sama'
            ]);
        }
        
        // Generate new token if none exists
        $newToken = Token::create([
            'token' => Token::generateRandomToken(),
            'expires_at' => Carbon::now()->addHour(),
            'is_active' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'token' => $newToken->token,
            'expires_at' => $newToken->expires_at->format('Y-m-d H:i:s'),
            'message' => 'Token baru dibuat'
        ]);
    }

    private function generateRandomToken()
    {
        return Token::generateRandomToken();
    }

    public function studentLogout(Request $request)
    {
        // Fully invalidate the session so exam progress cannot leak
        // when someone logs in with a different student account on the same browser.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('simulasi.login')->with('success', 'Anda telah logout');
    }

    public function confirmData(Request $request)
    {
        $request->validate([
            'jenis_kelamin' => 'required|in:L,P',
            'mata_ujian' => 'required|string',
            'nama_peserta' => 'required|string',
            'hari' => 'required|integer|min:1|max:31',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
            'token' => 'required|string|size:6',
        ]);

        // Check if student is logged in
        if (!Session::has('student_id')) {
            return redirect()->route('simulasi.login')->with('error', 'Silakan login terlebih dahulu');
        }

        $validToken = null;

        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var FirestoreTokenStore $tokenStore */
                $tokenStore = app(FirestoreTokenStore::class);
                $validToken = $tokenStore->findValidByToken($request->token);
            } catch (\Throwable $e) {
                $validToken = null;
            }
        }

        // Fallback to SQLite during migration
        if (!$validToken) {
            $validToken = Token::where('token', $request->token)
                ->where('is_active', true)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($validToken && $this->firestoreStudentPrimary()) {
                $this->syncTokenToFirestore($validToken);
            }
        }

        if (!$validToken) {
            return redirect()->back()->with('error', 'Token tidak valid atau sudah expired')->withInput();
        }

        // Store confirmation data in session
        Session::put('exam_data', [
            'jenis_kelamin' => $request->jenis_kelamin,
            'mata_ujian' => $request->mata_ujian,
            'nama_peserta' => $request->nama_peserta,
            'tanggal_lahir' => $request->tahun . '-' . str_pad($request->bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($request->hari, 2, '0', STR_PAD_LEFT),
        ]);
        
        // Get simulasi to create exam session
        $mataUjianName = $request->mata_ujian;
        $mataPelajaran = null;

        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var FirestoreMataPelajaranStore $mpStore */
                $mpStore = app(FirestoreMataPelajaranStore::class);
                $mataPelajaran = $mpStore->findByNama($mataUjianName);
            } catch (\Throwable $e) {
                $mataPelajaran = null;
            }
        }

        if (!$mataPelajaran) {
            $dbMp = MataPelajaran::where('nama', $mataUjianName)->first();
            if ($dbMp) {
                $mataPelajaran = $dbMp;
                if ($this->firestoreStudentPrimary()) {
                    try {
                        /** @var FirestoreMataPelajaranStore $mpStore */
                        $mpStore = app(FirestoreMataPelajaranStore::class);
                        $mpStore->upsertById((int) $dbMp->id, $dbMp->getAttributes());
                    } catch (\Throwable $e) {
                        // best-effort
                    }
                }
            }
        }
        
        $mataPelajaranId = null;
        if (is_array($mataPelajaran)) {
            $mataPelajaranId = (int) ($mataPelajaran['id'] ?? 0);
        } elseif ($mataPelajaran) {
            $mataPelajaranId = (int) $mataPelajaran->id;
        }

        if ($mataPelajaranId) {
            $simulasi = null;

            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var FirestoreSimulasiStore $simStore */
                    $simStore = app(FirestoreSimulasiStore::class);
                    $simulasi = $simStore->findLatestActiveByMataPelajaranId($mataPelajaranId);
                } catch (\Throwable $e) {
                    $simulasi = null;
                }
            }

            if (!$simulasi) {
                $dbSim = Simulasi::where('is_active', true)
                    ->where('mata_pelajaran_id', $mataPelajaranId)
                    ->latest()
                    ->first();

                if ($dbSim) {
                    $simulasi = $dbSim;
                    if ($this->firestoreStudentPrimary()) {
                        try {
                            /** @var FirestoreSimulasiStore $simStore */
                            $simStore = app(FirestoreSimulasiStore::class);
                            $simStore->upsertById((int) $dbSim->id, $dbSim->getAttributes());
                        } catch (\Throwable $e) {
                            // best-effort
                        }
                    }
                }
            }
            
            if ($simulasi) {
                $simulasiId = is_array($simulasi) ? (int) ($simulasi['id'] ?? 0) : (int) $simulasi->id;
                $userId = (int) Session::get('student_id');

                if ($this->firestoreStudentPrimary()) {
                    try {
                        /** @var FirestoreExamSessionStore $es */
                        $es = app(FirestoreExamSessionStore::class);
                        $es->upsert($userId, $simulasiId, [
                            'status' => 'logged_in',
                            'last_activity' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        // fallback below
                        \App\Models\ExamSession::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'simulasi_id' => $simulasiId,
                            ],
                            [
                                'status' => 'logged_in',
                                'last_activity' => now(),
                            ]
                        );
                    }
                } else {
                    \App\Models\ExamSession::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'simulasi_id' => $simulasiId,
                        ],
                        [
                            'status' => 'logged_in',
                            'last_activity' => now(),
                        ]
                    );
                }
            }
        }
        
        return redirect()->route('simulasi.exam');
    }

    public function startExam(Request $request)
    {
        // Check if student is logged in
        if (!Session::has('student_id')) {
            return redirect()->route('simulasi.login')->with('error', 'Silakan login terlebih dahulu');
        }

        return redirect()->route('simulasi.exam');
    }

    public function examInterface()
    {
        // Check if student is logged in
        if (!Session::has('student_id')) {
            return redirect()->route('simulasi.login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Check if exam data exists
        if (!Session::has('exam_data')) {
            return redirect()->route('simulasi.student.dashboard')->with('error', 'Silakan konfirmasi data terlebih dahulu');
        }

        $student = User::find(Session::get('student_id'));
        $examData = Session::get('exam_data');

        // Get active simulasi based on mata_ujian from exam_data
        $mataUjianName = $examData['mata_ujian'];
        $mataPelajaran = MataPelajaran::where('nama', $mataUjianName)->first();

        if (!$mataPelajaran) {
             return redirect()->route('simulasi.student.dashboard')
                ->with('error', "Mata pelajaran '{$mataUjianName}' tidak ditemukan dalam sistem.");
        }

        $simulasi = Simulasi::where('is_active', true)
                           ->where('mata_pelajaran_id', $mataPelajaran->id)
                           ->with(['mataPelajaran'])
                           ->latest()
                           ->first();

        if (!$simulasi) {
            return redirect()->route('simulasi.student.dashboard')
                ->with('error', "Tidak ada simulasi ujian aktif untuk mata pelajaran {$mataUjianName}. Silakan hubungi proktor/admin untuk generate simulasi.");
        }

        $simulasiPesertaKey = null;
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);
                $simulasiPesertaKey = $sp->key((int) $student->id, (int) $simulasi->id);

                $fsPeserta = $sp->getByUserSimulasi((int) $student->id, (int) $simulasi->id);
                if (is_array($fsPeserta) && (($fsPeserta['status'] ?? null) === 'selesai')) {
                    return redirect()->route('simulasi.student.dashboard')
                        ->with('error', 'Anda telah menyelesaikan simulasi ujian ini. Tidak dapat mengerjakan ulang.');
                }

                // If belum_mulai or not exists, set in_progress
                if (!is_array($fsPeserta)) {
                    $sp->upsert((int) $student->id, (int) $simulasi->id, [
                        'status' => 'sedang_mengerjakan',
                        'waktu_mulai' => now(),
                    ]);
                } elseif (($fsPeserta['status'] ?? null) === 'belum_mulai') {
                    $sp->upsert((int) $student->id, (int) $simulasi->id, [
                        'status' => 'sedang_mengerjakan',
                        'waktu_mulai' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                $simulasiPesertaKey = null;
            }
        }

        // Cek apakah data peserta sudah ada (status apapun)
        $existingPeserta = DB::table('simulasi_peserta')
            ->where('simulasi_id', $simulasi->id)
            ->where('user_id', $student->id)
            ->first();

        if ($existingPeserta) {
            // Jika sudah selesai, tolak akses
            if ($existingPeserta->status === 'selesai') {
                return redirect()->route('simulasi.student.dashboard')
                    ->with('error', 'Anda telah menyelesaikan simulasi ujian ini. Tidak dapat mengerjakan ulang.');
            }
            
            // Jika belum selesai, lanjutkan (resume)
            $simulasiPesertaId = $existingPeserta->id;
            $simulasiPeserta = $existingPeserta;

             // Update status to sedang_mengerjakan if belum_mulai
            if ($simulasiPeserta->status === 'belum_mulai') {
                DB::table('simulasi_peserta')
                    ->where('id', $simulasiPesertaId)
                    ->update([
                        'status' => 'sedang_mengerjakan',
                        'waktu_mulai' => now(),
                        'updated_at' => now(),
                    ]);
                    
                // Update exam session status
                if ($this->firestoreStudentPrimary()) {
                    try {
                        /** @var FirestoreExamSessionStore $es */
                        $es = app(FirestoreExamSessionStore::class);
                        $es->upsert((int) $student->id, (int) $simulasi->id, [
                            'status' => 'in_progress',
                            'started_at' => now(),
                            'last_activity' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        \App\Models\ExamSession::updateOrCreate(
                            [
                                'user_id' => $student->id,
                                'simulasi_id' => $simulasi->id,
                            ],
                            [
                                'status' => 'in_progress',
                                'started_at' => now(),
                                'last_activity' => now(),
                            ]
                        );
                    }
                } else {
                    \App\Models\ExamSession::updateOrCreate(
                        [
                            'user_id' => $student->id,
                            'simulasi_id' => $simulasi->id,
                        ],
                        [
                            'status' => 'in_progress',
                            'started_at' => now(),
                            'last_activity' => now(),
                        ]
                    );
                }
            }
        } else {
            // Create new simulasi_peserta record
            $simulasiPesertaId = DB::table('simulasi_peserta')->insertGetId([
                'simulasi_id' => $simulasi->id,
                'user_id' => $student->id,
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Create exam session
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var FirestoreExamSessionStore $es */
                    $es = app(FirestoreExamSessionStore::class);
                    $es->upsert((int) $student->id, (int) $simulasi->id, [
                        'status' => 'in_progress',
                        'started_at' => now(),
                        'last_activity' => now(),
                    ]);
                } catch (\Throwable $e) {
                    \App\Models\ExamSession::updateOrCreate(
                        [
                            'user_id' => $student->id,
                            'simulasi_id' => $simulasi->id,
                        ],
                        [
                            'status' => 'in_progress',
                            'started_at' => now(),
                            'last_activity' => now(),
                        ]
                    );
                }
            } else {
                \App\Models\ExamSession::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'simulasi_id' => $simulasi->id,
                    ],
                    [
                        'status' => 'in_progress',
                        'started_at' => now(),
                        'last_activity' => now(),
                    ]
                );
            }
        }

        // Keep Firestore simulasi_peserta in sync (dual-write) using the SQLite id field.
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);
                $simulasiPesertaKey = $simulasiPesertaKey ?: $sp->key((int) $student->id, (int) $simulasi->id);
                $payload = [
                    'id' => (int) $simulasiPesertaId,
                    'status' => 'sedang_mengerjakan',
                    'waktu_mulai' => now(),
                ];
                $sp->upsert((int) $student->id, (int) $simulasi->id, $payload);
            } catch (\Throwable $e) {
                // ignore
            }
        }
        
        // Update exam_data session to include simulasi_peserta_id
        $examData['simulasi_peserta_id'] = $simulasiPesertaId;
        if ($simulasiPesertaKey) {
            $examData['simulasi_peserta_key'] = $simulasiPesertaKey;
        }
        $examData['simulasi_id'] = $simulasi->id;
        Session::put('exam_data', $examData);
        
        // Get all soal and flatten sub_soal menjadi soal individual
        $soals = collect();

        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreSimulasiSoalStore $sstore */
                $sstore = app(\App\Services\FirestoreSimulasiSoalStore::class);
                /** @var \App\Services\FirestoreBankSoalStore $bank */
                $bank = app(\App\Services\FirestoreBankSoalStore::class);

                $refs = $sstore->listSoalRefsBySimulasiId((int) $simulasi->id, 2000);
                foreach ($refs as $ref) {
                    $soal = $bank->getSoalById((int) $ref['soal_id']);
                    if (!is_array($soal)) {
                        continue;
                    }

                    $soalPilihan = $bank->listPilihanJawabanBySoalId((int) $soal['id'], 500);
                    $subSoal = $bank->listSubSoalBySoalId((int) $soal['id'], 1000);
                    foreach ($subSoal as $idx => $sub) {
                        $subId = (int) ($sub['id'] ?? 0);
                        $subSoal[$idx]['pilihan_jawaban'] = $subId > 0
                            ? $bank->listSubPilihanJawabanBySubSoalId($subId, 500)
                            : [];
                    }

                    // Apply the same grouping logic using Firestore arrays.
                    if (!empty($subSoal)) {
                        $subSoals = collect($subSoal)->values();
                        $currentIndex = 0;
                        $totalSub = $subSoals->count();

                        while ($currentIndex < $totalSub) {
                            $currentSub = $subSoals[$currentIndex];
                            $type = (string) ($currentSub['jenis_soal'] ?? '');

                            $groupableTypes = ['benar_salah', 'mcma', 'pilihan_ganda_kompleks'];
                            $isGroupable = in_array($type, $groupableTypes, true);

                            if ($isGroupable) {
                                $groupItems = collect([$currentSub]);
                                $nextIndex = $currentIndex + 1;

                                $getNormalizedType = function ($t) {
                                    return ($t === 'pilihan_ganda_kompleks') ? 'mcma' : $t;
                                };
                                $currentNormalizedType = $getNormalizedType($type);

                                while ($nextIndex < $totalSub) {
                                    $nextSub = $subSoals[$nextIndex];
                                    $nextNormalizedType = $getNormalizedType((string) ($nextSub['jenis_soal'] ?? ''));
                                    if ($nextNormalizedType === $currentNormalizedType) {
                                        $groupItems->push($nextSub);
                                        $nextIndex++;
                                    } else {
                                        break;
                                    }
                                }

                                $groupData = [
                                    'id' => 'group_' . ((int) ($currentSub['id'] ?? 0)),
                                    'real_id' => (int) ($currentSub['id'] ?? 0),
                                    'parent_soal_id' => (int) ($soal['id'] ?? 0),
                                    'jenis_soal' => 'grouped',
                                    'pertanyaan' => ($currentNormalizedType === 'benar_salah' && $groupItems->count() > 1)
                                        ? 'Perhatikan pernyataan-pernyataan berikut:'
                                        : ($currentSub['pertanyaan'] ?? 'Pertanyaan'),
                                    'gambar_pertanyaan' => $currentSub['gambar_pertanyaan'] ?? null,
                                    'pilihan_jawaban' => $currentSub['pilihan_jawaban'] ?? [],
                                    'sub_soal' => $groupItems->map(function ($sub) use ($soal) {
                                        return [
                                            'id' => 'sub_' . ((int) ($sub['id'] ?? 0)),
                                            'real_id' => (int) ($sub['id'] ?? 0),
                                            'parent_soal_id' => (int) ($soal['id'] ?? 0),
                                            'nomor_urut' => (int) ($sub['nomor_urut'] ?? 0),
                                            'jenis_soal' => $sub['jenis_soal'] ?? null,
                                            'pertanyaan' => ($sub['pertanyaan'] ?? ''),
                                            'gambar_pertanyaan' => $sub['gambar_pertanyaan'] ?? null,
                                            'pilihan_jawaban' => $sub['pilihan_jawaban'] ?? [],
                                        ];
                                    })->toArray(),
                                    'is_grouped' => true,
                                ];

                                $soals->push($groupData);
                                $currentIndex = $nextIndex;
                            } else {
                                $soals->push([
                                    'id' => 'sub_' . ((int) ($currentSub['id'] ?? 0)),
                                    'sub_soal_id' => (int) ($currentSub['id'] ?? 0),
                                    'parent_soal_id' => (int) ($soal['id'] ?? 0),
                                    'nomor_urut' => (int) ($currentSub['nomor_urut'] ?? 0),
                                    'jenis_soal' => $currentSub['jenis_soal'] ?? null,
                                    'pertanyaan' => ($currentSub['pertanyaan'] ?? ''),
                                    'gambar_pertanyaan' => $currentSub['gambar_pertanyaan'] ?? null,
                                    'pilihan_jawaban' => $currentSub['pilihan_jawaban'] ?? [],
                                    'sub_soal' => [],
                                    'is_sub_soal' => true,
                                ]);
                                $currentIndex++;
                            }
                        }
                    } else {
                        $soals->push([
                            'id' => (int) ($soal['id'] ?? 0),
                            'parent_soal_id' => (int) ($soal['id'] ?? 0),
                            'jenis_soal' => $soal['jenis_soal'] ?? null,
                            'pertanyaan' => $soal['pertanyaan'] ?? null,
                            'gambar_pertanyaan' => $soal['gambar_pertanyaan'] ?? null,
                            'pilihan_jawaban' => $soalPilihan,
                            'is_sub_soal' => false,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Firestore failure: fall back to SQLite relationship loading below.
            }
        }

        if ($soals->isEmpty()) {
            $simulasi = Simulasi::where('is_active', true)
                               ->where('mata_pelajaran_id', $mataPelajaran->id)
                               ->with([
                                   'mataPelajaran', 
                                   'simulasiSoal.soal.pilihanJawaban',
                                   'simulasiSoal.soal.subSoal' => function($query) {
                                       $query->orderBy('nomor_urut');
                                   },
                                   'simulasiSoal.soal.subSoal.pilihanJawaban'
                               ])
                               ->latest()
                               ->first();

            foreach ($simulasi->simulasiSoal as $ss) {
                $soal = $ss->soal;
            
            // Check if packet (has sub-questions)
            if ($soal->subSoal && $soal->subSoal->count() > 0) {
                // Intelligent Grouping Logic
                // Group consecutive sub-questions of type 'benar_salah' or 'mcma' into a single user-facing question.
                
                // Ensure values are re-indexed to 0, 1, 2...
                $subSoals = $soal->subSoal->values();
                $currentIndex = 0;
                $totalSub = $subSoals->count();
                
                while ($currentIndex < $totalSub) {
                    $currentSub = $subSoals[$currentIndex];
                    $type = $currentSub->jenis_soal;
                    
                    // Determine if this type should be grouped
                    // We allow mixing B/S and MCMA in one group (Packet View)
                    $groupableTypes = ['benar_salah', 'mcma', 'pilihan_ganda_kompleks'];
                    $isGroupable = in_array($type, $groupableTypes);
                    
                    if ($isGroupable) {
                        // Start a group
                        $groupItems = collect([$currentSub]);
                        $nextIndex = $currentIndex + 1;
                        
                        // Helper to normalize type for grouping (treat mcma and pilihan_ganda_kompleks as same)
                        $getNormalizedType = function($t) {
                            return ($t === 'pilihan_ganda_kompleks') ? 'mcma' : $t;
                        };
                        
                        $currentNormalizedType = $getNormalizedType($type);
                        
                        // Look ahead for SAME normalized type
                        while ($nextIndex < $totalSub) {
                            $nextSub = $subSoals[$nextIndex];
                            $nextNormalizedType = $getNormalizedType($nextSub->jenis_soal);
                            
                            if ($nextNormalizedType === $currentNormalizedType) {
                                $groupItems->push($nextSub);
                                $nextIndex++;
                            } else {
                                break;
                            }
                        }
                        
                        // Construct Group Object
                        $groupData = [
                            'id' => 'group_' . $currentSub->id, 
                            'real_id' => $currentSub->id,
                            'parent_soal_id' => $soal->id,
                            'jenis_soal' => 'grouped', // Special type for Frontend
                            'pertanyaan' => ($currentNormalizedType === 'benar_salah' && $groupItems->count() > 1) 
                                            ? 'Perhatikan pernyataan-pernyataan berikut:' 
                                            : ($currentSub->pertanyaan ?? 'Pertanyaan'), // Use first item text
                            'gambar_pertanyaan' => $currentSub->gambar_pertanyaan,
                            'pilihan_jawaban' => $currentSub->pilihanJawaban,
                            'sub_soal' => $groupItems->map(function($sub) use ($soal) {
                                return [
                                    'id' => 'sub_' . $sub->id,
                                    'real_id' => $sub->id,
                                    'parent_soal_id' => $soal->id,
                                    'nomor_urut' => $sub->nomor_urut,
                                    'jenis_soal' => $sub->jenis_soal,
                                    'pertanyaan' => $sub->pertanyaan,
                                    'gambar_pertanyaan' => $sub->gambar_pertanyaan,
                                    'pilihan_jawaban' => $sub->pilihanJawaban,
                                ];
                            })->toArray(),
                            'is_grouped' => true
                        ];
                        
                        $soals->push($groupData);
                        $currentIndex = $nextIndex; // Skip processed items
                        
                    } else {
                        // Non-groupable (e.g. standard multiple choice), push as single
                        $soals->push([
                            'id' => 'sub_' . $currentSub->id,
                            'sub_soal_id' => $currentSub->id,
                            'parent_soal_id' => $soal->id,
                            'nomor_urut' => $currentSub->nomor_urut,
                            'jenis_soal' => $currentSub->jenis_soal,
                            'pertanyaan' => $currentSub->pertanyaan,
                            'gambar_pertanyaan' => $currentSub->gambar_pertanyaan,
                            'pilihan_jawaban' => $currentSub->pilihanJawaban,
                            'sub_soal' => [], 
                            'is_sub_soal' => true,
                        ]);
                        $currentIndex++;
                    }
                }
            } else {
                 // Jika tidak ada sub_soal, gunakan soal utama (Standalone)
                 $soals->push([
                    'id' => $soal->id,
                    'parent_soal_id' => $soal->id,
                    'jenis_soal' => $soal->jenis_soal,
                    'pertanyaan' => $soal->pertanyaan,
                    'gambar_pertanyaan' => $soal->gambar_pertanyaan,
                    'pilihan_jawaban' => $soal->pilihanJawaban,
                    'is_sub_soal' => false,
                ]);
            }
            }
        }

        // Store the soal IDs used for this exam as a reliable fallback for scoring/review.
        // This prevents score=0 when Firestore/relationship lookups fail during finishExam.
        $examSoalIds = $soals
            ->map(function ($row) {
                if (is_array($row)) {
                    return (int) (($row['parent_soal_id'] ?? null) ?: ($row['id'] ?? 0));
                }

                // Shouldn't happen (we pass arrays to the view), but keep safe.
                return (int) (($row->parent_soal_id ?? null) ?: ($row->id ?? 0));
            })
            ->filter(fn ($id) => (int) $id > 0)
            ->unique()
            ->values()
            ->all();
        Session::put('exam_soal_ids', $examSoalIds);

        // Initialize answers in session if not exists
        if (!Session::has('exam_answers')) {
            Session::put('exam_answers', []);
        }

        return view('simulasi.exam', compact('student', 'simulasi', 'soals', 'examData'));
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'soal_id' => 'required',
            'jawaban' => 'present', // Bisa string, array list (MCMA), atau map (Benar/Salah)
        ]);

        $answers = Session::get('exam_answers', []);
        if (!is_array($answers)) {
            $answers = [];
        }

        $jawaban = $request->jawaban;

        // Normalize: list-array answers (MCMA) => comma string.
        // Keep associative array answers intact (Benar/Salah per option).
        if (is_array($jawaban)) {
            if (array_is_list($jawaban)) {
                $jawaban = implode(',', $jawaban);
            }
        }

        $answers[$request->input('soal_id')] = $jawaban;
        Session::put('exam_answers', $answers);

        // Best-effort: persist per-soal answers to Firestore to avoid loss on session issues.
        $studentId = (int) Session::get('student_id');
        $simulasiId = (int) ($request->input('simulasi_id') ?? 0);
        if ($simulasiId <= 0) {
            $examData = Session::get('exam_data', []);
            if (is_array($examData)) {
                $simulasiId = (int) ($examData['simulasi_id'] ?? 0);
            }
        }

        if ($this->firestoreStudentPrimary() && $studentId > 0 && $simulasiId > 0) {
            try {
                $simulasiPesertaId = null;
                $examData = Session::get('exam_data', []);
                if (is_array($examData) && !empty($examData['simulasi_peserta_id'])) {
                    $simulasiPesertaId = (int) $examData['simulasi_peserta_id'];
                }
                if (!$simulasiPesertaId) {
                    $simulasiPesertaId = (int) (DB::table('simulasi_peserta')
                        ->where('simulasi_id', $simulasiId)
                        ->where('user_id', $studentId)
                        ->value('id') ?? 0);
                }

                /** @var FirestoreJawabanPesertaStore $js */
                $js = app(FirestoreJawabanPesertaStore::class);
                $js->upsert($studentId, $simulasiId, (int) $request->input('soal_id'), [
                    'simulasi_peserta_id' => $simulasiPesertaId ?: null,
                    'jawaban_user' => $jawaban,
                ]);
            } catch (\Throwable $e) {
                // ignore Firestore persistence failures
            }
        }

        return response()->json(['success' => true]);
    }

    public function finishExam(Request $request)
    {
        try {
            \Log::info('Finish exam called');
            \Log::info('Request data: ', $request->all());
            
            DB::beginTransaction();
            
            $studentId = Session::get('student_id');
            $user = User::find($studentId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi login siswa tidak valid atau telah berakhir.'
                ], 401);
            }

            $examData = Session::get('exam_data');
            
            // Ambil jawaban dari Session
            $sessionAnswers = Session::get('exam_answers', []);
            
            // Ambil jawaban dari Request (backup jika session gagal update/race condition)
            $requestAnswers = $request->input('answers', []);
            
            // Merge jawaban (prioritas request jika ada barunya)
            // Konversi array list di request (MCMA) ke string.
            // Penting: untuk Benar/Salah tipe tabel, jawaban berupa associative array
            // (optId => 'B'/'S' atau 'benar'/'salah') dan HARUS dipertahankan.
            $formattedRequestAnswers = [];
            foreach ($requestAnswers as $key => $val) {
                if (is_array($val)) {
                    // PHP 8.1+: list array means numeric sequential keys (e.g. ['A','C'])
                    if (array_is_list($val)) {
                        $formattedRequestAnswers[$key] = implode(',', $val);
                    } else {
                        // Keep associative map (e.g. { optionId: 'B'/'S' })
                        $formattedRequestAnswers[$key] = $val;
                    }
                    continue;
                }

                $formattedRequestAnswers[$key] = $val;
            }
            
            // Gabungkan: Session ditimpa Request (karena Request adalah state terakhir di frontend)
            // IMPORTANT: do NOT use array_merge here because it reindexes numeric keys (soal_id),
            // which would break answer mapping and yield 0 score.
            $jawabanPeserta = array_replace($sessionAnswers, $formattedRequestAnswers);
            
            // Update session dengan data terbaru
            Session::put('exam_answers', $jawabanPeserta);
            
            \Log::info('Exam data: ', ['exam_data' => $examData, 'answers' => $jawabanPeserta]);

            if (!$examData || (!isset($examData['simulasi_peserta_id']) && !isset($examData['simulasi_peserta_key']))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data ujian tidak ditemukan'
                ], 400);
            }

            $simulasiPeserta = null;
            if (isset($examData['simulasi_peserta_id'])) {
                $simulasiPeserta = DB::table('simulasi_peserta')
                    ->where('id', $examData['simulasi_peserta_id'])
                    ->first();
            }
            if (!$simulasiPeserta && isset($examData['simulasi_id'])) {
                $simulasiPeserta = DB::table('simulasi_peserta')
                    ->where('simulasi_id', $examData['simulasi_id'])
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (!$simulasiPeserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan'
                ], 400);
            }

            // Ambil simulasi dan soal-soal
            $simulasi = Simulasi::find($simulasiPeserta->simulasi_id);
            $soalIds = [];
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var \App\Services\FirestoreSimulasiSoalStore $sstore */
                    $sstore = app(\App\Services\FirestoreSimulasiSoalStore::class);
                    $refs = $sstore->listSoalRefsBySimulasiId((int) $simulasiPeserta->simulasi_id, 2000);
                    $soalIds = collect($refs)->pluck('soal_id')->all();
                } catch (\Throwable $e) {
                    $soalIds = [];
                }
            }
            if (empty($soalIds)) {
                $simWith = Simulasi::with('simulasiSoal')->find($simulasiPeserta->simulasi_id);
                $soalIds = $simWith?->simulasiSoal?->pluck('soal_id')?->toArray() ?? [];
            }

            // Last-resort fallback: use the IDs captured when rendering the exam.
            if (empty($soalIds)) {
                $soalIds = Session::get('exam_soal_ids', []);
                if (!is_array($soalIds)) {
                    $soalIds = [];
                }
            }

            // If answers are missing, try to load cached per-soal answers from Firestore.
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var FirestoreJawabanPesertaStore $js */
                    $js = app(FirestoreJawabanPesertaStore::class);
                    $cached = $js->listByUserSimulasi((int) $user->id, (int) $simulasiPeserta->simulasi_id, 2000);
                    foreach ($cached as $row) {
                        $sid = (int) ($row['soal_id'] ?? 0);
                        if ($sid <= 0) {
                            continue;
                        }
                        if (!array_key_exists($sid, $jawabanPeserta) || $jawabanPeserta[$sid] === null || $jawabanPeserta[$sid] === '') {
                            $jawabanPeserta[$sid] = $row['jawaban_user'] ?? null;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // Hitung nilai
            if ($this->firestoreStudentPrimary()) {
                /** @var \App\Services\FirestorePenilaianService $fps */
                $fps = app(\App\Services\FirestorePenilaianService::class);
                $hasilPenilaian = $fps->hitungNilai($jawabanPeserta, $soalIds);

                // Fallback: Firestore-first might be enabled, but Firestore may not have soal data yet.
                // If Firestore returns too few questions (e.g. paket treated as 1), recompute using DB logic.
                $expectedJumlah = $this->expectedJumlahSoalFromDb($soalIds);
                $fsJumlah = (int) ($hasilPenilaian['jumlah_soal'] ?? 0);
                if ($fsJumlah === 0 || ($expectedJumlah > 0 && $fsJumlah < $expectedJumlah)) {
                    try {
                        $fallbackService = new PenilaianService();
                        $fallback = $fallbackService->hitungNilai($jawabanPeserta, $soalIds);
                        if ((int) ($fallback['jumlah_soal'] ?? 0) > 0) {
                            $hasilPenilaian = $fallback;
                        }
                    } catch (\Throwable $e) {
                        // keep Firestore result
                    }
                }
            } else {
                $penilaianService = new PenilaianService();
                $hasilPenilaian = $penilaianService->hitungNilai($jawabanPeserta, $soalIds);
            }

            // Normalize: nilai_total should be a percentage.
            $skorPoin = $this->computeSkorPoin($hasilPenilaian);
            $skorMaks = $this->computeSkorMaksimal($hasilPenilaian);
            $nilaiPersen = $skorMaks > 0 ? round(($skorPoin / $skorMaks) * 100) : 0;

            $hasilPenilaian['skor_poin'] = $skorPoin;
            $hasilPenilaian['skor_maksimal'] = $skorMaks;
            $hasilPenilaian['nilai_total'] = $nilaiPersen;
            // Keep legacy counters aligned with skor poin.
            $hasilPenilaian['jumlah_benar'] = (int) round($skorPoin);

            // Simpan nilai ke database
            $nilai = Nilai::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'simulasi_id' => $simulasi->id,
                ],
                [
                    'mata_pelajaran_id' => $simulasi->mata_pelajaran_id,
                    'nilai_total' => $hasilPenilaian['nilai_total'],
                    'jumlah_benar' => $hasilPenilaian['jumlah_benar'],
                    'jumlah_salah' => $hasilPenilaian['jumlah_salah'],
                    'jumlah_soal' => $hasilPenilaian['jumlah_soal'],
                    'detail_jawaban' => $hasilPenilaian['detail_jawaban'],
                ]
            );

            // Update status simulasi peserta
            DB::table('simulasi_peserta')
                ->where('id', $simulasiPeserta->id)
                ->update([
                    'status' => 'selesai',
                    'waktu_selesai' => now(),
                    'nilai' => $hasilPenilaian['nilai_total'],
                    'updated_at' => now(),
                ]);

            // Firestore: persist nilai + jawaban + peserta (Firestore-first, still dual-write to SQLite for migration)
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var \App\Services\FirestoreNilaiStore $ns */
                    $ns = app(\App\Services\FirestoreNilaiStore::class);
                    $ns->upsert((int) $user->id, (int) $simulasi->id, [
                        'id' => (int) $nilai->id,
                        'mata_pelajaran_id' => (int) $simulasi->mata_pelajaran_id,
                        'nilai_total' => (float) $hasilPenilaian['nilai_total'],
                        'jumlah_benar' => (int) $hasilPenilaian['jumlah_benar'],
                        'jumlah_salah' => (int) $hasilPenilaian['jumlah_salah'],
                        'jumlah_soal' => (int) $hasilPenilaian['jumlah_soal'],
                        'detail_jawaban' => $hasilPenilaian['detail_jawaban'],
                    ]);

                    /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                    $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);
                    $sp->upsert((int) $user->id, (int) $simulasi->id, [
                        'id' => (int) $simulasiPeserta->id,
                        'status' => 'selesai',
                        'waktu_mulai' => $simulasiPeserta->waktu_mulai ? \Carbon\Carbon::parse($simulasiPeserta->waktu_mulai) : null,
                        'waktu_selesai' => now(),
                        'nilai' => (float) $hasilPenilaian['nilai_total'],
                    ]);

                    /** @var \App\Services\FirestoreJawabanPesertaStore $js */
                    $js = app(\App\Services\FirestoreJawabanPesertaStore::class);
                    foreach ($hasilPenilaian['detail_jawaban'] as $row) {
                        $sid = (int) ($row['soal_id'] ?? 0);
                        if ($sid <= 0) {
                            continue;
                        }
                        $js->upsert((int) $user->id, (int) $simulasi->id, $sid, [
                            'simulasi_peserta_id' => (int) $simulasiPeserta->id,
                            'jenis_soal' => $row['jenis_soal'] ?? null,
                            'jawaban_user' => $row['jawaban_user'] ?? null,
                            'jawaban_benar' => $row['jawaban_benar'] ?? null,
                            'nilai' => $row['nilai'] ?? null,
                            'maksimal' => $row['maksimal'] ?? null,
                            'detail' => $row['detail'] ?? null,
                        ]);
                    }
                } catch (\Throwable $e) {
                    // ignore Firestore failures during migration
                }
            }
                
            // Update exam session to completed
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var FirestoreExamSessionStore $es */
                    $es = app(FirestoreExamSessionStore::class);
                    $es->upsert((int) $user->id, (int) $simulasi->id, [
                        'status' => 'completed',
                        'submitted_at' => now(),
                        'last_activity' => now(),
                    ]);
                } catch (\Throwable $e) {
                    \App\Models\ExamSession::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'simulasi_id' => $simulasi->id,
                        ],
                        [
                            'status' => 'completed',
                            'submitted_at' => now(),
                            'last_activity' => now(),
                        ]
                    );
                }
            } else {
                \App\Models\ExamSession::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'simulasi_id' => $simulasi->id,
                    ],
                    [
                        'status' => 'completed',
                        'submitted_at' => now(),
                        'last_activity' => now(),
                    ]
                );
            }

            // Simpan hasil ke session untuk ditampilkan
            Session::put('hasil_ujian', [
                'nilai_id' => $nilai->id,
                'nilai_total' => $hasilPenilaian['nilai_total'],
                'jumlah_benar' => $hasilPenilaian['jumlah_benar'],
                'jumlah_salah' => $hasilPenilaian['jumlah_salah'],
                'jumlah_soal' => $hasilPenilaian['jumlah_soal'],
                'skor_poin' => $hasilPenilaian['skor_poin'] ?? $hasilPenilaian['jumlah_benar'],
                'skor_maksimal' => $hasilPenilaian['skor_maksimal'] ?? $hasilPenilaian['jumlah_soal'],
                'detail_jawaban' => $hasilPenilaian['detail_jawaban'],
                'nama_simulasi' => $simulasi->nama_simulasi,
                'mata_pelajaran' => $simulasi->mataPelajaran->nama_mata_pelajaran,
            ]);

            // Simpan data untuk review (jangan hapus exam_data dulu)
            Session::put('review_data', [
                'soal_ids' => $soalIds,
                'jawaban' => $jawabanPeserta,
                'hasil' => $hasilPenilaian,
                'simulasi' => [
                    'nama' => $simulasi->nama_simulasi,
                    'mata_pelajaran' => $simulasi->mataPelajaran->nama_mata_pelajaran,
                ]
            ]);

            DB::commit();
            
            // Return JSON for AJAX request
            return response()->json([
                'success' => true,
                'redirect' => route('simulasi.review')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error finish exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    public function review(PenilaianService $penilaianService)
    {
        $reviewData = Session::get('review_data');

        // Fallback source: finishExam stores a compact summary in hasil_ujian.
        $hasilUjian = Session::get('hasil_ujian');
        if (!is_array($hasilUjian)) {
            $hasilUjian = null;
        }

        // Get student_id early to check for latest nilai as fallback
        $studentId = (int) Session::get('student_id');
        $latestNilai = null;
        if ($studentId > 0) {
            $latestNilai = Nilai::query()
                ->where('user_id', $studentId)
                ->latest()
                ->first();
        }
        
        if (!$reviewData) {
            // If review_data is missing but hasil_ujian exists, allow rendering anyway.
            if ($hasilUjian) {
                $reviewData = [
                    'soal_ids' => [],
                    'jawaban' => Session::get('exam_answers', []),
                    'hasil' => [
                        'nilai_total' => $hasilUjian['nilai_total'] ?? 0,
                        'jumlah_benar' => $hasilUjian['jumlah_benar'] ?? 0,
                        'jumlah_salah' => $hasilUjian['jumlah_salah'] ?? 0,
                        'jumlah_soal' => $hasilUjian['jumlah_soal'] ?? 0,
                        'detail_jawaban' => $hasilUjian['detail_jawaban'] ?? [],
                    ],
                    'simulasi' => [
                        'nama' => $hasilUjian['nama_simulasi'] ?? '-',
                        'mata_pelajaran' => $hasilUjian['mata_pelajaran'] ?? '-',
                    ],
                ];
            } elseif ($latestNilai) {
                // If no session data but student has nilai, load from database
                $detailJawaban = $this->normalizeDetailJawaban($latestNilai->detail_jawaban ?? null);
                $sim = Simulasi::with('mataPelajaran')->find((int) $latestNilai->simulasi_id);
                $reviewData = [
                    'soal_ids' => [],
                    'jawaban' => Session::get('exam_answers', []),
                    'hasil' => [
                        'nilai_total' => (float) ($latestNilai->nilai_total ?? 0),
                        'jumlah_benar' => (int) ($latestNilai->jumlah_benar ?? 0),
                        'jumlah_salah' => (int) ($latestNilai->jumlah_salah ?? 0),
                        'jumlah_soal' => (int) ($latestNilai->jumlah_soal ?? 0),
                        'detail_jawaban' => $detailJawaban,
                    ],
                    'simulasi' => [
                        'nama' => $sim?->nama_simulasi ?? '-',
                        'mata_pelajaran' => $sim?->mataPelajaran?->nama_mata_pelajaran ?? '-',
                    ],
                ];
            } else {
                return redirect()->route('simulasi.student.dashboard')
                    ->with('error', 'Data review tidak ditemukan');
            }
        }

        // If review_data exists but empty/invalid, or session was refreshed, load latest nilai for student.
        $needsNilaiFallback = !is_array($reviewData)
            || empty($reviewData['hasil'])
            || !is_array($reviewData['hasil'])
            || empty($reviewData['hasil']['detail_jawaban']);
        if ($needsNilaiFallback && $latestNilai) {
            $detailJawaban = $this->normalizeDetailJawaban($latestNilai->detail_jawaban ?? null);
            $sim = Simulasi::with('mataPelajaran')->find((int) $latestNilai->simulasi_id);
            $reviewData = [
                'soal_ids' => [],
                'jawaban' => Session::get('exam_answers', []),
                'hasil' => [
                    'nilai_total' => (float) ($latestNilai->nilai_total ?? 0),
                    'jumlah_benar' => (int) ($latestNilai->jumlah_benar ?? 0),
                    'jumlah_salah' => (int) ($latestNilai->jumlah_salah ?? 0),
                    'jumlah_soal' => (int) ($latestNilai->jumlah_soal ?? 0),
                    'detail_jawaban' => $detailJawaban,
                ],
                'simulasi' => [
                    'nama' => $sim?->nama_simulasi ?? '-',
                    'mata_pelajaran' => $sim?->mataPelajaran?->nama_mata_pelajaran ?? '-',
                ],
            ];
        }

        $examData = Session::get('exam_data', []);
        $simulasiId = 0;
        if (is_array($examData)) {
            $simulasiId = (int) ($examData['simulasi_id'] ?? 0);
        }
        if ($simulasiId <= 0 && $hasilUjian && !empty($hasilUjian['nilai_id'])) {
            $nilaiRow = Nilai::find((int) $hasilUjian['nilai_id']);
            if ($nilaiRow) {
                $simulasiId = (int) $nilaiRow->simulasi_id;
            }
        }
        if ($simulasiId <= 0 && !empty($reviewData['hasil'])) {
            $nilaiRow = null;
            if (!empty($reviewData['hasil']['nilai_id'])) {
                $nilaiRow = Nilai::find((int) $reviewData['hasil']['nilai_id']);
            }
            if ($nilaiRow) {
                $simulasiId = (int) $nilaiRow->simulasi_id;
            }
        }

        $jawaban = $reviewData['jawaban'] ?? [];
        if (!is_array($jawaban)) {
            $jawaban = [];
        }

        // If jawaban is empty, try to load from Firestore cached per-soal answers.
        if (empty($jawaban) && $this->firestoreStudentPrimary()) {
            $studentId = (int) Session::get('student_id');
            if ($studentId > 0 && $simulasiId > 0) {
                try {
                    /** @var FirestoreJawabanPesertaStore $js */
                    $js = app(FirestoreJawabanPesertaStore::class);
                    $cached = $js->listByUserSimulasi($studentId, $simulasiId, 2000);
                    foreach ($cached as $row) {
                        $sid = (int) ($row['soal_id'] ?? 0);
                        if ($sid <= 0) {
                            continue;
                        }
                        $jawaban[$sid] = $row['jawaban_user'] ?? null;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        $soalIds = $reviewData['soal_ids'] ?? [];
        if (!is_array($soalIds)) {
            $soalIds = [];
        }
        $soalIds = array_values($soalIds);

        // Fallback: if session review_data does not contain IDs, use the IDs captured at exam render time.
        if (empty($soalIds)) {
            $soalIds = Session::get('exam_soal_ids', []);
            if (!is_array($soalIds)) {
                $soalIds = [];
            }
            $soalIds = array_values($soalIds);
        }

        // Fallback: if still empty and simulasi_id known, derive soal IDs from simulasi.
        if (empty($soalIds) && $simulasiId > 0) {
            $simWith = Simulasi::with('simulasiSoal')->find($simulasiId);
            if ($simWith && $simWith->simulasiSoal) {
                $soalIds = $simWith->simulasiSoal->pluck('soal_id')->filter()->map(fn ($v) => (int) $v)->unique()->values()->all();
            }
        }

        // Fallback: jika soal_ids kosong, coba derive dari detail_jawaban yang tersimpan.
        $savedHasil = $reviewData['hasil'] ?? null;
        if (is_array($savedHasil) && isset($savedHasil['detail_jawaban'])) {
            $savedHasil['detail_jawaban'] = $this->normalizeDetailJawaban($savedHasil['detail_jawaban']);
        }
        if ($latestNilai && (int) ($latestNilai->jumlah_soal ?? 0) > 0) {
            $latestDetail = $this->normalizeDetailJawaban($latestNilai->detail_jawaban ?? null);
            $savedHasil = [
                'nilai_total' => (float) ($latestNilai->nilai_total ?? 0),
                'jumlah_benar' => (int) ($latestNilai->jumlah_benar ?? 0),
                'jumlah_salah' => (int) ($latestNilai->jumlah_salah ?? 0),
                'jumlah_soal' => (int) ($latestNilai->jumlah_soal ?? 0),
                'detail_jawaban' => $latestDetail,
            ];

            if (empty($soalIds) && !empty($latestDetail)) {
                $soalIds = collect($latestDetail)
                    ->pluck('soal_id')
                    ->filter(fn ($v) => (int) $v > 0)
                    ->map(fn ($v) => (int) $v)
                    ->unique()
                    ->values()
                    ->all();
            }
        }
        if (empty($soalIds) && is_array($savedHasil) && isset($savedHasil['detail_jawaban']) && is_array($savedHasil['detail_jawaban'])) {
            $soalIds = collect($savedHasil['detail_jawaban'])
                ->pluck('soal_id')
                ->filter(fn ($v) => (int) $v > 0)
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->all();
        }

        // Extra fallback: derive IDs from hasil_ujian (more reliable across session changes).
        if (empty($soalIds) && $hasilUjian && isset($hasilUjian['detail_jawaban']) && is_array($hasilUjian['detail_jawaban'])) {
            $soalIds = collect($hasilUjian['detail_jawaban'])
                ->pluck('soal_id')
                ->filter(fn ($v) => (int) $v > 0)
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->all();
        }
        
        // If no answers are available or recalculation would be empty, use saved hasil directly.
        $hasil = null;
        // ALWAYS use savedHasil if it exists and has valid data (from latestNilai or reviewData)
        if (is_array($savedHasil) && (int) ($savedHasil['jumlah_soal'] ?? 0) > 0) {
            $hasil = $savedHasil;
        }

        if (!$hasil) {
            // RE-CALCULATE to ensure latest logic (e.g. Table View / Detail generation) is applied
            // regardless of what was saved in Session previously
            if ($this->firestoreStudentPrimary()) {
                /** @var \App\Services\FirestorePenilaianService $fps */
                $fps = app(\App\Services\FirestorePenilaianService::class);
                $hasil = $fps->hitungNilai($jawaban, $soalIds);

                // Fallback: if Firestore scoring yields no questions, use DB scoring.
                if ((int) ($hasil['jumlah_soal'] ?? 0) === 0) {
                    try {
                        $fallback = $penilaianService->hitungNilai($jawaban, $soalIds);
                        if ((int) ($fallback['jumlah_soal'] ?? 0) > 0) {
                            $hasil = $fallback;
                        }
                    } catch (\Throwable $e) {
                        // keep Firestore result
                    }
                }
            } else {
                $hasil = $penilaianService->hitungNilai($jawaban, $soalIds);
            }
        }

        // If recalculation unexpectedly yields no questions, fall back to saved hasil.
        if ((int) ($hasil['jumlah_soal'] ?? 0) === 0 && is_array($savedHasil) && (int) ($savedHasil['jumlah_soal'] ?? 0) > 0) {
            $hasil = $savedHasil;
            if (empty($soalIds) && isset($hasil['detail_jawaban']) && is_array($hasil['detail_jawaban'])) {
                $soalIds = collect($hasil['detail_jawaban'])
                    ->pluck('soal_id')
                    ->filter(fn ($v) => (int) $v > 0)
                    ->map(fn ($v) => (int) $v)
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        // If still empty, fall back to hasil_ujian.
        if ((int) ($hasil['jumlah_soal'] ?? 0) === 0 && $hasilUjian && (int) ($hasilUjian['jumlah_soal'] ?? 0) > 0) {
            $hasil = [
                'nilai_total' => $hasilUjian['nilai_total'] ?? 0,
                'jumlah_benar' => $hasilUjian['jumlah_benar'] ?? 0,
                'jumlah_salah' => $hasilUjian['jumlah_salah'] ?? 0,
                'jumlah_soal' => $hasilUjian['jumlah_soal'] ?? 0,
                'detail_jawaban' => $hasilUjian['detail_jawaban'] ?? [],
            ];

            if (empty($soalIds) && isset($hasil['detail_jawaban']) && is_array($hasil['detail_jawaban'])) {
                $soalIds = collect($hasil['detail_jawaban'])
                    ->pluck('soal_id')
                    ->filter(fn ($v) => (int) $v > 0)
                    ->map(fn ($v) => (int) $v)
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        // Load soal dengan jawaban (gunakan soalIds final)
        $soals = empty($soalIds)
            ? collect()
            : Soal::with(['subSoal.pilihanJawaban', 'pilihanJawaban'])
                ->whereIn('id', $soalIds)
                ->get();
        
        // Calculate score + percent value
        $skorPoin = $this->computeSkorPoin($hasil);
        $skorMaksimal = $this->computeSkorMaksimal($hasil);
        $nilai = $skorMaksimal > 0 ? round(($skorPoin / $skorMaksimal) * 100) : 0;

        // Student identity (for review header)
        $student = null;
        $studentId = Session::get('student_id');
        if ($studentId) {
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var \App\Services\FirestoreUserStore $store */
                    $store = app(\App\Services\FirestoreUserStore::class);
                    $fsStudent = $store->findById((int) $studentId);
                    if (is_array($fsStudent)) {
                        $student = (object) $fsStudent;
                    }
                } catch (\Throwable $e) {
                    $student = null;
                }
            }

            if (!$student) {
                $student = User::find((int) $studentId);
            }
        }

        return view('simulasi.review', compact('soals', 'jawaban', 'hasil', 'reviewData', 'nilai', 'skorPoin', 'skorMaksimal', 'student'));
    }

    public function finishReview()
    {
        // Hapus semua session data setelah review selesai
        Session::forget('exam_answers');
        Session::forget('exam_data');
        Session::forget('review_data');
        Session::forget('hasil_ujian');

        return redirect()->route('simulasi.student.dashboard')
            ->with('success', 'Ujian berhasil diselesaikan');
    }

    public function hasilUjian()
    {
        $hasilUjian = Session::get('hasil_ujian');
        
        if (!$hasilUjian) {
            return redirect()->route('simulasi.student.dashboard')
                ->with('error', 'Data hasil ujian tidak ditemukan');
        }

        return view('student.hasil-ujian', compact('hasilUjian'));
    }

    public function riwayatNilai()
    {
        $user = Auth::user();

        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreNilaiStore $ns */
                $ns = app(\App\Services\FirestoreNilaiStore::class);
                /** @var \App\Services\FirestoreSimulasiStore $ss */
                $ss = app(\App\Services\FirestoreSimulasiStore::class);
                /** @var \App\Services\FirestoreMataPelajaranStore $ms */
                $ms = app(\App\Services\FirestoreMataPelajaranStore::class);

                $rows = $ns->listByUserId((int) $user->id, 500);
                $riwayatNilai = collect($rows)->map(function (array $row) use ($ss, $ms) {
                    $createdAt = $row['created_at'] ?? null;
                    if (!($createdAt instanceof \Carbon\Carbon) && $createdAt) {
                        try {
                            $createdAt = \Carbon\Carbon::parse($createdAt);
                        } catch (\Throwable $e) {
                            $createdAt = null;
                        }
                    }

                    $simulasiId = (int) ($row['simulasi_id'] ?? 0);
                    $mataId = (int) ($row['mata_pelajaran_id'] ?? 0);

                    $simulasiData = $simulasiId > 0 ? $ss->getById($simulasiId) : null;
                    $mataData = $mataId > 0 ? $ms->getById($mataId) : null;

                    $simulasiName = (string) (($simulasiData['nama_simulasi'] ?? null) ?: '');
                    if ($simulasiName === '' && $simulasiId > 0) {
                        $sqlSim = \App\Models\Simulasi::find($simulasiId);
                        $simulasiName = (string) ($sqlSim?->nama_simulasi ?? '');
                    }

                    $mataNama = (string) (($mataData['nama_mata_pelajaran'] ?? null) ?: ($mataData['nama'] ?? null) ?: '');
                    if ($mataNama === '' && $mataId > 0) {
                        $sqlM = \App\Models\MataPelajaran::find($mataId);
                        $mataNama = (string) ($sqlM?->nama ?? '');
                    }

                    $nilaiObj = (object) [
                        'id' => (int) ($row['id'] ?? 0),
                        'user_id' => (int) ($row['user_id'] ?? 0),
                        'simulasi_id' => $simulasiId,
                        'mata_pelajaran_id' => $mataId,
                        'nilai_total' => (float) ($row['nilai_total'] ?? 0),
                        'jumlah_benar' => (int) ($row['jumlah_benar'] ?? 0),
                        'jumlah_salah' => (int) ($row['jumlah_salah'] ?? 0),
                        'jumlah_soal' => (int) ($row['jumlah_soal'] ?? 0),
                        'detail_jawaban' => $row['detail_jawaban'] ?? [],
                        'created_at' => $createdAt instanceof \Carbon\Carbon ? $createdAt : now(),
                        'simulasi' => (object) [
                            'id' => $simulasiId,
                            'nama_simulasi' => $simulasiName,
                        ],
                        'mataPelajaran' => (object) [
                            'id' => $mataId,
                            // Views in this repo use both `nama` and `nama_mata_pelajaran` in different places.
                            'nama' => $mataNama,
                            'nama_mata_pelajaran' => $mataNama,
                        ],
                    ];

                    return $nilaiObj;
                });

                return view('student.riwayat-nilai', compact('riwayatNilai'));
            } catch (\Throwable $e) {
                // fall back to SQLite below
            }
        }

        $riwayatNilai = Nilai::with(['simulasi', 'mataPelajaran'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.riwayat-nilai', compact('riwayatNilai'));
    }

    public function detailNilai($id)
    {
        $user = Auth::user();

        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreNilaiStore $ns */
                $ns = app(\App\Services\FirestoreNilaiStore::class);
                /** @var \App\Services\FirestoreSimulasiStore $ss */
                $ss = app(\App\Services\FirestoreSimulasiStore::class);
                /** @var \App\Services\FirestoreMataPelajaranStore $ms */
                $ms = app(\App\Services\FirestoreMataPelajaranStore::class);

                $row = $ns->getBySqliteId((int) $id);
                if (!is_array($row) || (int) ($row['user_id'] ?? 0) !== (int) $user->id) {
                    abort(404);
                }

                $createdAt = $row['created_at'] ?? null;
                if (!($createdAt instanceof \Carbon\Carbon) && $createdAt) {
                    try {
                        $createdAt = \Carbon\Carbon::parse($createdAt);
                    } catch (\Throwable $e) {
                        $createdAt = null;
                    }
                }

                $simulasiId = (int) ($row['simulasi_id'] ?? 0);
                $mataId = (int) ($row['mata_pelajaran_id'] ?? 0);

                $simulasiData = $simulasiId > 0 ? $ss->getById($simulasiId) : null;
                $mataData = $mataId > 0 ? $ms->getById($mataId) : null;

                $simulasiName = (string) (($simulasiData['nama_simulasi'] ?? null) ?: '');
                if ($simulasiName === '' && $simulasiId > 0) {
                    $sqlSim = \App\Models\Simulasi::find($simulasiId);
                    $simulasiName = (string) ($sqlSim?->nama_simulasi ?? '');
                }

                $mataNama = (string) (($mataData['nama_mata_pelajaran'] ?? null) ?: ($mataData['nama'] ?? null) ?: '');
                if ($mataNama === '' && $mataId > 0) {
                    $sqlM = \App\Models\MataPelajaran::find($mataId);
                    $mataNama = (string) ($sqlM?->nama ?? '');
                }

                $nilai = (object) [
                    'id' => (int) ($row['id'] ?? 0),
                    'user_id' => (int) ($row['user_id'] ?? 0),
                    'simulasi_id' => $simulasiId,
                    'mata_pelajaran_id' => $mataId,
                    'nilai_total' => (float) ($row['nilai_total'] ?? 0),
                    'jumlah_benar' => (int) ($row['jumlah_benar'] ?? 0),
                    'jumlah_salah' => (int) ($row['jumlah_salah'] ?? 0),
                    'jumlah_soal' => (int) ($row['jumlah_soal'] ?? 0),
                    'detail_jawaban' => $row['detail_jawaban'] ?? [],
                    'created_at' => $createdAt instanceof \Carbon\Carbon ? $createdAt : now(),
                    'simulasi' => (object) [
                        'id' => $simulasiId,
                        'nama_simulasi' => $simulasiName,
                    ],
                    'mataPelajaran' => (object) [
                        'id' => $mataId,
                        'nama' => $mataNama,
                        'nama_mata_pelajaran' => $mataNama,
                    ],
                ];

                $detailJawaban = $nilai->detail_jawaban;
                if (is_string($detailJawaban)) {
                    $detailJawaban = json_decode($detailJawaban, true);
                }
                if (!is_array($detailJawaban)) {
                    $detailJawaban = [];
                }

                // Load soal untuk menampilkan detail
                $soalIds = array_column($detailJawaban, 'soal_id');
                $soalList = Soal::with(['pilihanJawaban', 'subSoal.subPilihanJawaban'])
                    ->whereIn('id', $soalIds)
                    ->get()
                    ->keyBy('id');

                return view('student.detail-nilai', compact('nilai', 'detailJawaban', 'soalList'));
            } catch (\Throwable $e) {
                // fall back to SQLite below
            }
        }

        $nilai = Nilai::with(['simulasi', 'mataPelajaran'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $detailJawaban = json_decode($nilai->detail_jawaban, true);
        
        // Load soal untuk menampilkan detail
        $soalIds = array_column($detailJawaban, 'soal_id');
        $soalList = Soal::with(['pilihanJawaban', 'subSoal.subPilihanJawaban'])
            ->whereIn('id', $soalIds)
            ->get()
            ->keyBy('id');

        return view('student.detail-nilai', compact('nilai', 'detailJawaban', 'soalList'));
    }

    // Student Monitoring Methods
    public function examList()
    {
        // Get all simulasi with participant and completion counts
        $simulasiList = Simulasi::with('mataPelajaran')
            ->where('is_active', true)
            ->orderBy('waktu_mulai', 'desc')
            ->get();

        // Add participant and completed counts
        foreach ($simulasiList as $simulasi) {
            // Count participants from simulasi_peserta (registered students)
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                    $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);
                    $pesertaRows = $sp->listBySimulasiId((int) $simulasi->id, 2000);
                    $simulasi->participant_count = collect($pesertaRows)
                        ->pluck('user_id')
                        ->filter(fn ($v) => is_numeric($v) && (int) $v > 0)
                        ->unique()
                        ->count();
                } catch (\Throwable $e) {
                    $simulasi->participant_count = DB::table('simulasi_peserta')
                        ->where('simulasi_id', $simulasi->id)
                        ->distinct('user_id')
                        ->count('user_id');
                }
            } else {
                $simulasi->participant_count = DB::table('simulasi_peserta')
                    ->where('simulasi_id', $simulasi->id)
                    ->distinct('user_id')
                    ->count('user_id');
            }

            // Count completed students: those with nilai OR status='selesai' in simulasi_peserta
            $completedUserIds = collect();
            
            // Get user IDs from nilai (both Firestore and SQLite)
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var \App\Services\FirestoreNilaiStore $ns */
                    $ns = app(\App\Services\FirestoreNilaiStore::class);
                    $nilaiRows = $ns->listBySimulasiId((int) $simulasi->id, 2000);
                    $firestoreUserIds = collect($nilaiRows)
                        ->pluck('user_id')
                        ->filter(fn ($v) => is_numeric($v) && (int) $v > 0)
                        ->map(fn ($v) => (int) $v);
                    $completedUserIds = $completedUserIds->merge($firestoreUserIds);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
            
            // Also check SQLite nilai
            $sqliteNilaiUserIds = Nilai::where('simulasi_id', $simulasi->id)
                ->pluck('user_id')
                ->filter(fn ($v) => (int) $v > 0)
                ->map(fn ($v) => (int) $v);
            $completedUserIds = $completedUserIds->merge($sqliteNilaiUserIds);
            
            // Also check simulasi_peserta with status='selesai' (both Firestore and SQLite)
            if ($this->firestoreStudentPrimary()) {
                try {
                    /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                    $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);
                    $pesertaRows = $sp->listBySimulasiId((int) $simulasi->id, 2000);
                    $firestoreSelesaiUserIds = collect($pesertaRows)
                        ->filter(fn ($row) => ($row['status'] ?? '') === 'selesai')
                        ->pluck('user_id')
                        ->filter(fn ($v) => is_numeric($v) && (int) $v > 0)
                        ->map(fn ($v) => (int) $v);
                    $completedUserIds = $completedUserIds->merge($firestoreSelesaiUserIds);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
            
            $sqliteSelesaiUserIds = DB::table('simulasi_peserta')
                ->where('simulasi_id', $simulasi->id)
                ->where('status', 'selesai')
                ->pluck('user_id')
                ->filter(fn ($v) => (int) $v > 0)
                ->map(fn ($v) => (int) $v);
            $completedUserIds = $completedUserIds->merge($sqliteSelesaiUserIds);
            
            $simulasi->completed_count = $completedUserIds->unique()->count();
        }

        return view('simulasi.exam-list', compact('simulasiList'));
    }

    public function studentStatus($simulasiId)
    {
        $simulasi = Simulasi::with('mataPelajaran')->findOrFail($simulasiId);

        // Only show students that are registered for this simulasi.
        $participantIds = collect();
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);
                $rows = $sp->listBySimulasiId((int) $simulasiId, 2000);
                $participantIds = collect($rows)
                    ->pluck('user_id')
                    ->filter(fn ($v) => is_numeric($v) && (int) $v > 0)
                    ->map(fn ($v) => (int) $v)
                    ->unique()
                    ->values();
            } catch (\Throwable $e) {
                // fallback to SQLite
                if (Schema::hasTable('simulasi_peserta')) {
                    $participantIds = DB::table('simulasi_peserta')
                        ->where('simulasi_id', $simulasiId)
                        ->pluck('user_id');
                }
            }
        } else {
            if (Schema::hasTable('simulasi_peserta')) {
                $participantIds = DB::table('simulasi_peserta')
                    ->where('simulasi_id', $simulasiId)
                    ->pluck('user_id');
            }
        }

        $students = User::query()
            ->where('role', 'siswa')
            ->when($participantIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $participantIds))
            ->when($participantIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->orderBy('rombongan_belajar')
            ->orderBy('name')
            ->get();

        // Get exam sessions for this simulasi
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var FirestoreExamSessionStore $es */
                $es = app(FirestoreExamSessionStore::class);
                $examSessions = collect($es->listBySimulasiId((int) $simulasiId))
                    ->map(function ($row) {
                        $row = is_array($row) ? $row : [];
                        $row = array_merge([
                            'user_id' => 0,
                            'status' => null,
                            'started_at' => null,
                            'submitted_at' => null,
                            'last_activity' => null,
                        ], $row);

                        foreach (['started_at', 'submitted_at', 'last_activity'] as $k) {
                            $v = $row[$k] ?? null;
                            if ($v && !($v instanceof \Carbon\Carbon)) {
                                try {
                                    $row[$k] = \Carbon\Carbon::parse($v);
                                } catch (\Throwable $e) {
                                    $row[$k] = null;
                                }
                            }
                        }

                        return (object) $row;
                    })
                    ->keyBy('user_id');
            } catch (\Throwable $e) {
                $examSessions = collect();
            }
        } else {
            $examSessions = \App\Models\ExamSession::where('simulasi_id', $simulasiId)
                ->with('user')
                ->get()
                ->keyBy('user_id');
        }

        // Get peserta status (from Firestore if available, else SQLite)
        $pesertaRecords = collect();
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);
                $rows = $sp->listBySimulasiId((int) $simulasiId, 2000);
                $pesertaRecords = collect($rows)
                    ->map(function ($row) {
                        $row = is_array($row) ? $row : [];
                        $row = array_merge([
                            'user_id' => 0,
                            'status' => null,
                            'waktu_mulai' => null,
                            'waktu_selesai' => null,
                            'nilai' => null,
                        ], $row);

                        foreach (['waktu_mulai', 'waktu_selesai'] as $k) {
                            $v = $row[$k] ?? null;
                            if ($v && !($v instanceof \Carbon\Carbon)) {
                                try {
                                    $row[$k] = \Carbon\Carbon::parse($v);
                                } catch (\Throwable $e) {
                                    $row[$k] = null;
                                }
                            }
                        }

                        return (object) $row;
                    })
                    ->keyBy('user_id');
            } catch (\Throwable $e) {
                $pesertaRecords = collect();
            }
        }

        if ($pesertaRecords->isEmpty() && Schema::hasTable('simulasi_peserta')) {
            $pesertaRecords = DB::table('simulasi_peserta')
                ->where('simulasi_id', $simulasiId)
                ->get()
                ->keyBy('user_id');
        }

        $sqlitePesertaRecords = collect();
        if (Schema::hasTable('simulasi_peserta')) {
            $sqlitePesertaRecords = DB::table('simulasi_peserta')
                ->where('simulasi_id', $simulasiId)
                ->get()
                ->keyBy('user_id');
        }

        // Get nilai records
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreNilaiStore $ns */
                $ns = app(\App\Services\FirestoreNilaiStore::class);
                $rows = $ns->listBySimulasiId((int) $simulasiId, 2000);
                $nilaiRecords = collect($rows)->mapWithKeys(function (array $row) {
                    $createdAt = $row['created_at'] ?? null;
                    if (!($createdAt instanceof \Carbon\Carbon) && $createdAt) {
                        try {
                            $createdAt = \Carbon\Carbon::parse($createdAt);
                        } catch (\Throwable $e) {
                            $createdAt = null;
                        }
                    }

                    $uid = (int) ($row['user_id'] ?? 0);
                    if ($uid <= 0) {
                        return [];
                    }

                    return [
                        $uid => (object) [
                            'id' => (int) ($row['id'] ?? 0),
                            'user_id' => $uid,
                            'simulasi_id' => (int) ($row['simulasi_id'] ?? 0),
                            'nilai_total' => (float) ($row['nilai_total'] ?? 0),
                            'created_at' => $createdAt instanceof \Carbon\Carbon ? $createdAt : now(),
                        ],
                    ];
                });
            } catch (\Throwable $e) {
                $nilaiRecords = Nilai::where('simulasi_id', $simulasiId)
                    ->with('user')
                    ->get()
                    ->keyBy('user_id');
            }
        } else {
            $nilaiRecords = Nilai::where('simulasi_id', $simulasiId)
                ->with('user')
                ->get()
                ->keyBy('user_id');
        }

        $sqliteNilaiRecords = Nilai::where('simulasi_id', $simulasiId)
            ->with('user')
            ->get()
            ->keyBy('user_id');

        // Get unique classes (from registered students)
        $classes = $students->pluck('rombongan_belajar')->unique()->sort()->values();

        // Combine data
        $studentData = $students->map(function ($student) use ($examSessions, $nilaiRecords, $pesertaRecords, $sqlitePesertaRecords, $sqliteNilaiRecords) {
            $session = $examSessions->get($student->id);
            $nilai = $nilaiRecords->get($student->id);
            $peserta = $pesertaRecords->get($student->id);
            $sqlitePeserta = $sqlitePesertaRecords->get($student->id);
            $sqliteNilai = $sqliteNilaiRecords->get($student->id);

            if (!$nilai && $sqliteNilai) {
                $nilai = $sqliteNilai;
            }

            $pesertaStatus = null;
            if ($peserta) {
                $pesertaStatus = (string) ($peserta->status ?? '');
            }
            $sqliteStatus = null;
            if ($sqlitePeserta) {
                $sqliteStatus = (string) ($sqlitePeserta->status ?? '');
            }

            // Determine status
            if ($nilai || $pesertaStatus === 'selesai' || $sqliteStatus === 'selesai') {
                $status = 'completed';
                $statusText = 'Selesai';
                $statusColor = 'success';
            } elseif ($pesertaStatus === 'sedang_mengerjakan' || $sqliteStatus === 'sedang_mengerjakan') {
                $status = 'working';
                $statusText = 'Sedang Mengerjakan';
                $statusColor = 'warning';
            } elseif ($pesertaStatus === 'belum_mulai' || $sqliteStatus === 'belum_mulai') {
                $status = 'not_started';
                $statusText = 'Belum Login';
                $statusColor = 'secondary';
            } elseif ($session) {
                if ($session->status === 'in_progress') {
                    $status = 'working';
                    $statusText = 'Sedang Mengerjakan';
                    $statusColor = 'warning';
                } elseif ($session->status === 'reviewing') {
                    $status = 'reviewing';
                    $statusText = 'Review';
                    $statusColor = 'info';
                } else {
                    $status = 'logged_in';
                    $statusText = 'Sudah Login';
                    $statusColor = 'info';
                }
            } else {
                $status = 'not_started';
                $statusText = 'Belum Login';
                $statusColor = 'secondary';
            }

            return (object) [
                'student' => $student,
                'session' => $session,
                'nilai' => $nilai,
                'status' => $status,
                'statusText' => $statusText,
                'statusColor' => $statusColor,
            ];
        });

        return view('simulasi.student-status', compact('simulasi', 'studentData', 'classes'));
    }

    public function resetLogin(Request $request, $simulasiId, $userId)
    {
        // Find and delete exam session (forces re-login)
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var FirestoreExamSessionStore $es */
                $es = app(FirestoreExamSessionStore::class);
                $es->delete((int) $userId, (int) $simulasiId);
            } catch (\Throwable $e) {
                \App\Models\ExamSession::where('simulasi_id', $simulasiId)
                    ->where('user_id', $userId)
                    ->delete();
            }
        } else {
            \App\Models\ExamSession::where('simulasi_id', $simulasiId)
                ->where('user_id', $userId)
                ->delete();
        }

        // Also clear Laravel session if this is the current student
        if (Session::get('student_id') == $userId) {
            Session::forget('student_id');
            Session::forget('student_name');
            Session::forget('student_nisn');
        }

        return response()->json([
            'success' => true,
            'message' => 'Login siswa berhasil direset. Siswa harus login ulang.'
        ]);
    }

    public function resetProgress(Request $request, $simulasiId, $userId)
    {
        // Delete nilai record
        Nilai::where('simulasi_id', $simulasiId)
            ->where('user_id', $userId)
            ->delete();

        // Reset peserta record so the student can retake
        if (Schema::hasTable('simulasi_peserta')) {
            DB::table('simulasi_peserta')
                ->where('simulasi_id', $simulasiId)
                ->where('user_id', $userId)
                ->update([
                    'status' => 'belum_mulai',
                    'waktu_mulai' => null,
                    'waktu_selesai' => null,
                    'nilai' => null,
                    'updated_at' => now(),
                ]);
        }

        // Also remove per-soal answers if the table exists
        if (Schema::hasTable('jawaban_peserta')) {
            $peserta = DB::table('simulasi_peserta')
                ->where('simulasi_id', $simulasiId)
                ->where('user_id', $userId)
                ->first();
            if ($peserta) {
                DB::table('jawaban_peserta')
                    ->where('simulasi_peserta_id', $peserta->id)
                    ->delete();
            }
        }

        // Firestore cleanup/reset
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var \App\Services\FirestoreNilaiStore $ns */
                $ns = app(\App\Services\FirestoreNilaiStore::class);
                $ns->delete((int) $userId, (int) $simulasiId);

                /** @var \App\Services\FirestoreJawabanPesertaStore $js */
                $js = app(\App\Services\FirestoreJawabanPesertaStore::class);
                $js->deleteAllForUserSimulasi((int) $userId, (int) $simulasiId);

                /** @var \App\Services\FirestoreSimulasiPesertaStore $sp */
                $sp = app(\App\Services\FirestoreSimulasiPesertaStore::class);
                $sp->upsert((int) $userId, (int) $simulasiId, [
                    'status' => 'belum_mulai',
                    'waktu_mulai' => null,
                    'waktu_selesai' => null,
                    'nilai' => null,
                ]);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Delete exam session
        if ($this->firestoreStudentPrimary()) {
            try {
                /** @var FirestoreExamSessionStore $es */
                $es = app(FirestoreExamSessionStore::class);
                $es->delete((int) $userId, (int) $simulasiId);
            } catch (\Throwable $e) {
                \App\Models\ExamSession::where('simulasi_id', $simulasiId)
                    ->where('user_id', $userId)
                    ->delete();
            }
        } else {
            \App\Models\ExamSession::where('simulasi_id', $simulasiId)
                ->where('user_id', $userId)
                ->delete();
        }

        // Clear session if current student
        if (Session::get('student_id') == $userId) {
            Session::forget('student_id');
            Session::forget('student_name');
            Session::forget('student_nisn');
        }

        return response()->json([
            'success' => true,
            'message' => 'Progress siswa berhasil direset. Siswa dapat mengulang ujian dari awal.'
        ]);
    }
}
