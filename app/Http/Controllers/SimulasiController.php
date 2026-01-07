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
                    ->has('soal')
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
        
        // Get simulasi to create exam session
        $mataUjianName = $request->mata_ujian;
        $mataPelajaran = MataPelajaran::where('nama', $mataUjianName)->first();
        
        if ($mataPelajaran) {
            $simulasi = Simulasi::where('is_active', true)
                               ->where('mata_pelajaran_id', $mataPelajaran->id)
                               ->latest()
                               ->first();
            
            if ($simulasi) {
                // Create or update exam session
                \App\Models\ExamSession::updateOrCreate(
                    [
                        'user_id' => Session::get('student_id'),
                        'simulasi_id' => $simulasi->id,
                    ],
                    [
                        'status' => 'logged_in',
                        'last_activity' => now(),
                    ]
                );
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
                           ->with([
                               'mataPelajaran', 
                               'simulasiSoal.soal.pilihanJawaban',
                               'simulasiSoal.soal.subSoal' => function($query) {
                                   $query->orderBy('nomor_urut');
                               },
                               'simulasiSoal.soal.subSoal.pilihanJawaban'
                           ])
                           ->latest() // Get the latest one if multiple exist
                           ->first();

        if (!$simulasi) {
            return redirect()->route('simulasi.student.dashboard')
                ->with('error', "Tidak ada simulasi ujian aktif untuk mata pelajaran {$mataUjianName}. Silakan hubungi proktor/admin untuk generate simulasi.");
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
        
        // Update exam_data session to include simulasi_peserta_id
        $examData['simulasi_peserta_id'] = $simulasiPesertaId;
        $examData['simulasi_id'] = $simulasi->id;
        Session::put('exam_data', $examData);
        
        // Get all soal and flatten sub_soal menjadi soal individual
        $soals = collect();
        
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
                                    'pertanyaan' => $sub->pertanyaan . " [Type: {$sub->jenis_soal}]",
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
                            'pertanyaan' => $currentSub->pertanyaan . " [Single Type: {$currentSub->jenis_soal}]",
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
            'jawaban' => 'required', // Bisa string atau array untuk MCMA
        ]);

        $answers = Session::get('exam_answers', []);
        
        // Jawaban bisa berupa string (radio) atau array (checkbox untuk MCMA)
        $jawaban = $request->jawaban;
        
        // Jika array, konversi ke JSON string atau join dengan koma
        if (is_array($jawaban)) {
            $jawaban = implode(',', $jawaban);
        }
        
        $answers[$request->soal_id] = $jawaban;
        Session::put('exam_answers', $answers);

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
            // Konversi array di request (MCMA) ke string
            $formattedRequestAnswers = [];
            foreach ($requestAnswers as $key => $val) {
                if (is_array($val)) {
                    $formattedRequestAnswers[$key] = implode(',', $val);
                } else {
                    $formattedRequestAnswers[$key] = $val;
                }
            }
            
            // Gabungkan: Session ditimpa Request (karena Request adalah state terakhir di frontend)
            $jawabanPeserta = array_merge($sessionAnswers, $formattedRequestAnswers);
            
            // Update session dengan data terbaru
            Session::put('exam_answers', $jawabanPeserta);
            
            \Log::info('Exam data: ', ['exam_data' => $examData, 'answers' => $jawabanPeserta]);

            if (!$examData || !isset($examData['simulasi_peserta_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data ujian tidak ditemukan'
                ], 400);
            }

            $simulasiPeserta = DB::table('simulasi_peserta')
                ->where('id', $examData['simulasi_peserta_id'])
                ->first();

            if (!$simulasiPeserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan'
                ], 400);
            }

            // Ambil simulasi dan soal-soal
            $simulasi = Simulasi::with('simulasiSoal')->find($simulasiPeserta->simulasi_id);
            $soalIds = $simulasi->simulasiSoal->pluck('soal_id')->toArray();

            // Hitung nilai menggunakan PenilaianService
            $penilaianService = new PenilaianService();
            $hasilPenilaian = $penilaianService->hitungNilai($jawabanPeserta, $soalIds);

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
                    'detail_jawaban' => json_encode($hasilPenilaian['detail_jawaban']),
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
                
            // Update exam session to completed
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

            // Simpan hasil ke session untuk ditampilkan
            Session::put('hasil_ujian', [
                'nilai_id' => $nilai->id,
                'nilai_total' => $hasilPenilaian['nilai_total'],
                'jumlah_benar' => $hasilPenilaian['jumlah_benar'],
                'jumlah_salah' => $hasilPenilaian['jumlah_salah'],
                'jumlah_soal' => $hasilPenilaian['jumlah_soal'],
                'detail_jawaban' => $hasilPenilaian['detail_jawaban'],
                'nama_simulasi' => $simulasi->nama_simulasi,
                'mata_pelajaran' => $simulasi->mataPelajaran->nama_mata_pelajaran,
            ]);

            DB::commit();

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
        
        if (!$reviewData) {
            return redirect()->route('simulasi.student.dashboard')
                ->with('error', 'Data review tidak ditemukan');
        }

        // Load soal dengan jawaban
        $soals = Soal::with(['subSoal.pilihanJawaban', 'pilihanJawaban']) // Ensure 'pilihanJawaban' is loaded for Single Items
            ->whereIn('id', $reviewData['soal_ids'])
            ->get();

        $jawaban = $reviewData['jawaban'];
        
        // RE-CALCULATE to ensure latest logic (e.g. Table View / Detail generation) is applied
        // regardless of what was saved in Session previously
        $hasil = $penilaianService->hitungNilai($jawaban, $reviewData['soal_ids']);
        
        // Calculate score
        $skor = $hasil['jumlah_benar'];
        $skorMaksimal = $hasil['jumlah_soal'];
        $nilai = $hasil['nilai_total'];

        return view('simulasi.review', compact('soals', 'jawaban', 'hasil', 'reviewData', 'nilai'));
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
        
        $riwayatNilai = Nilai::with(['simulasi', 'mataPelajaran'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.riwayat-nilai', compact('riwayatNilai'));
    }

    public function detailNilai($id)
    {
        $user = Auth::user();
        
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
            ->orderBy('waktu_mulai', 'desc')
            ->get();

        // Add participant and completed counts manually
        foreach ($simulasiList as $simulasi) {
            $simulasi->participant_count = Nilai::where('simulasi_id', $simulasi->id)
                ->distinct('user_id')
                ->count('user_id');
            $simulasi->completed_count = Nilai::where('simulasi_id', $simulasi->id)->count();
        }

        return view('simulasi.exam-list', compact('simulasiList'));
    }

    public function studentStatus($simulasiId)
    {
        $simulasi = Simulasi::with('mataPelajaran')->findOrFail($simulasiId);
        
        // Get all students and their exam status
        $students = User::where('role', 'siswa')
            ->orderBy('rombongan_belajar')
            ->orderBy('name')
            ->get();

        // Get exam sessions for this simulasi
        $examSessions = \App\Models\ExamSession::where('simulasi_id', $simulasiId)
            ->with('user')
            ->get()
            ->keyBy('user_id');

        // Get nilai records
        $nilaiRecords = Nilai::where('simulasi_id', $simulasiId)
            ->with('user')
            ->get()
            ->keyBy('user_id');

        // Get unique classes
        $classes = $students->pluck('rombongan_belajar')->unique()->sort()->values();

        // Combine data
        $studentData = $students->map(function ($student) use ($examSessions, $nilaiRecords, $simulasiId) {
            $session = $examSessions->get($student->id);
            $nilai = $nilaiRecords->get($student->id);

            // Determine status
            if ($nilai) {
                $status = 'completed';
                $statusText = 'Selesai';
                $statusColor = 'success';
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
        \App\Models\ExamSession::where('simulasi_id', $simulasiId)
            ->where('user_id', $userId)
            ->delete();

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

        // Delete exam session
        \App\Models\ExamSession::where('simulasi_id', $simulasiId)
            ->where('user_id', $userId)
            ->delete();

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
