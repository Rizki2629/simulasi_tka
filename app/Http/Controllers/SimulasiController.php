<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use App\Models\Soal;
use App\Models\MataPelajaran;
use App\Models\Simulasi;
use App\Models\SimulasiSoal;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SimulasiController extends Controller
{
    public function generateSimulasi()
    {
        // Get all students from database
        $students = User::where('role', 'siswa')
                       ->orderBy('rombongan_belajar')
                       ->orderBy('name')
                       ->get();
        
        // Get paket soal grouped by mata pelajaran with soal count
        $paketSoal = MataPelajaran::where('is_active', true)
                    ->withCount('soal')
                    ->having('soal_count', '>', 0)
                    ->get()
                    ->map(function($mp, $index) {
                        // Generate kode paket format: MTK-20251114-143025
                        $prefix = strtoupper($mp->kode ?? substr($mp->nama, 0, 3));
                        $tanggal = now()->format('Ymd-His');
                        // Add index to make it unique
                        $uniqueCode = $index + 1;
                        return [
                            'id' => $mp->id,
                            'kode' => "{$prefix}-{$tanggal}-{$uniqueCode}",
                            'nama' => $mp->nama,
                            'jumlah_soal' => $mp->soal_count,
                            'label' => "{$prefix}-{$tanggal}-{$uniqueCode} - {$mp->nama} ({$mp->soal_count} Soal)"
                        ];
                    });
        
        return view('simulasi.generate', compact('students', 'paketSoal'));
    }

    public function storeSimulasi(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'nama_simulasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'durasi_menit' => 'required|integer|min:1',
            'peserta' => 'required|array|min:1',
            'peserta.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // Create simulasi
            $simulasi = Simulasi::create([
                'nama_simulasi' => $request->nama_simulasi,
                'deskripsi' => $request->deskripsi,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'durasi_menit' => $request->durasi_menit,
                'created_by' => Auth::id() ?? 1, // Default to admin if not logged in
                'is_active' => true,
            ]);

            // Get all soal from selected mata pelajaran
            $soals = Soal::where('mata_pelajaran_id', $request->mata_pelajaran_id)->get();

            // Add soal to simulasi_soal
            foreach ($soals as $index => $soal) {
                SimulasiSoal::create([
                    'simulasi_id' => $simulasi->id,
                    'soal_id' => $soal->id,
                    'urutan' => $index + 1,
                ]);
            }

            // Add peserta to simulasi_peserta (if table exists)
            // foreach ($request->peserta as $peserta_id) {
            //     SimulasiPeserta::create([
            //         'simulasi_id' => $simulasi->id,
            //         'user_id' => $peserta_id,
            //     ]);
            // }

            DB::commit();

            return redirect()->route('soal.index')->with('success', 'Simulasi berhasil di-generate! Soal telah ditandai sebagai aktif.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat simulasi: ' . $e->getMessage())->withInput();
        }
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

        // Find student by NISN
        $student = User::where('nisn', $request->nisn)
                      ->where('role', 'siswa')
                      ->first();

        // Check if student exists and password is correct
        if (!$student || !Hash::check($request->password, $student->password)) {
            return back()->with('error', 'NISN atau password salah')->withInput();
        }

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

        $student = User::find(Session::get('student_id'));

        if (!$student) {
            Session::flush();
            return redirect()->route('simulasi.login')->with('error', 'Session tidak valid');
        }

        // Get current active token from database
        $currentToken = Token::getCurrentToken();
        
        // If no active token or expired, create new one
        if (!$currentToken) {
            $currentToken = Token::create([
                'token' => Token::generateRandomToken(),
                'expires_at' => Carbon::now()->addHour(),
                'is_active' => true,
            ]);
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

    public function studentLogout()
    {
        Session::forget('student_id');
        Session::forget('student_name');
        Session::forget('student_nisn');
        
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

        // Validate token
        $validToken = Token::where('token', $request->token)
                          ->where('is_active', true)
                          ->where('expires_at', '>', Carbon::now())
                          ->first();

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
        // For now, we'll get the first active simulasi
        $simulasi = Simulasi::where('is_active', true)
                           ->with(['mataPelajaran', 'simulasiSoal.soal.pilihanJawaban'])
                           ->first();

        if (!$simulasi) {
            return redirect()->route('simulasi.student.dashboard')->with('error', 'Tidak ada simulasi aktif');
        }

        // Get all soal for this simulasi
        $soals = $simulasi->simulasiSoal->map(function($ss) {
            return $ss->soal;
        });

        // Initialize answers in session if not exists
        if (!Session::has('exam_answers')) {
            Session::put('exam_answers', []);
        }

        return view('simulasi.exam', compact('student', 'simulasi', 'soals', 'examData'));
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'soal_id' => 'required|integer',
            'jawaban' => 'required|string',
        ]);

        $answers = Session::get('exam_answers', []);
        $answers[$request->soal_id] = $request->jawaban;
        Session::put('exam_answers', $answers);

        return response()->json(['success' => true]);
    }

    public function finishExam(Request $request)
    {
        // Process exam results here
        Session::forget('exam_answers');
        Session::forget('exam_data');
        
        return redirect()->route('simulasi.student.dashboard')->with('success', 'Ujian selesai');
    }
}
