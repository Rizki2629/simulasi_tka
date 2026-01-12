<?php

namespace Tests\Feature;

use App\Models\MataPelajaran;
use App\Models\PilihanJawaban;
use App\Models\Simulasi;
use App\Models\SimulasiSoal;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExamFlowSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_flow_end_to_end_sqlite_mode(): void
    {
        // Ensure Firestore-first mode is OFF in this smoke.
        // The controller reads env('FIRESTORE_STUDENT_PRIMARY', true).
        putenv('FIRESTORE_STUDENT_PRIMARY=false');
        $_ENV['FIRESTORE_STUDENT_PRIMARY'] = 'false';
        $_SERVER['FIRESTORE_STUDENT_PRIMARY'] = 'false';

        // Seed minimal data.
        $admin = User::factory()->create();
        $student = User::factory()->create();

        $mapel = MataPelajaran::create([
            'nama' => 'Matematika',
            'kode' => 'MTK',
            'deskripsi' => null,
            'is_active' => true,
        ]);

        $simulasi = Simulasi::create([
            'nama_simulasi' => 'Simulasi MTK',
            'deskripsi' => null,
            'mata_pelajaran_id' => $mapel->id,
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->addHour(),
            'durasi_menit' => 30,
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $soal = Soal::create([
            'kode_soal' => 'SOAL-001',
            'mata_pelajaran_id' => $mapel->id,
            'jenis_soal' => 'pilihan_ganda',
            'pertanyaan' => '2 + 2 = ...',
            'gambar_pertanyaan' => null,
            'jawaban_benar' => 'A',
            'bobot' => 1,
            'created_by' => $admin->id,
        ]);

        PilihanJawaban::create([
            'soal_id' => $soal->id,
            'label' => 'A',
            'teks_jawaban' => '4',
            'gambar_jawaban' => null,
            'is_benar' => true,
        ]);
        PilihanJawaban::create([
            'soal_id' => $soal->id,
            'label' => 'B',
            'teks_jawaban' => '5',
            'gambar_jawaban' => null,
            'is_benar' => false,
        ]);

        SimulasiSoal::create([
            'simulasi_id' => $simulasi->id,
            'soal_id' => $soal->id,
            'urutan' => 1,
        ]);

        $simulasiPesertaId = DB::table('simulasi_peserta')->insertGetId([
            'simulasi_id' => $simulasi->id,
            'user_id' => $student->id,
            'status' => 'sedang_mengerjakan',
            'waktu_mulai' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Build the required session state for exam routes.
        $session = [
            'student_id' => $student->id,
            'exam_data' => [
                'jenis_kelamin' => 'L',
                'mata_ujian' => $mapel->nama,
                'nama_peserta' => $student->name ?? 'Siswa',
                'tanggal_lahir' => '2014-01-01',
                'simulasi_id' => $simulasi->id,
                'simulasi_peserta_id' => $simulasiPesertaId,
            ],
            'exam_answers' => [],
        ];

        // 1) Exam page renders.
        $respExam = $this->withSession($session)->get('/simulasi/exam');
        $respExam->assertStatus(200);
        $respExam->assertDontSee('Single Type:');
        $respExam->assertDontSee('[Type:');

        // 2) Submit an answer and finish exam.
        $this->postJson('/simulasi/submit-answer', [
            'soal_id' => $soal->id,
            'jawaban' => 'A',
        ])->assertStatus(200);

        $respFinish = $this->postJson('/simulasi/finish-exam', [
            'answers' => [
                $soal->id => 'A',
            ],
        ]);

        $respFinish->assertStatus(200);
        $respFinish->assertJson(['success' => true]);

        // Nilai should be stored as percentage.
        $this->assertDatabaseHas('nilai', [
            'user_id' => $student->id,
            'simulasi_id' => $simulasi->id,
            'nilai_total' => 100.00,
            'jumlah_benar' => 1,
        ]);

        // 3) Review page renders and shows details.
        $respReview = $this->get('/simulasi/review');
        $respReview->assertStatus(200);
        $respReview->assertSee('Review Hasil Simulasi');
        $respReview->assertSee('Detail Jawaban');
    }
}
