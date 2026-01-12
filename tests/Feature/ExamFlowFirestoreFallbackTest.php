<?php

namespace Tests\Feature;

use App\Models\MataPelajaran;
use App\Models\PilihanJawaban;
use App\Models\Simulasi;
use App\Models\SimulasiSoal;
use App\Models\Soal;
use App\Models\User;
use App\Models\Nilai;
use App\Services\FirestorePenilaianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExamFlowFirestoreFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_finish_exam_falls_back_to_db_scoring_when_firestore_returns_zero(): void
    {
        // Enable Firestore-first mode.
        putenv('FIRESTORE_STUDENT_PRIMARY=true');
        $_ENV['FIRESTORE_STUDENT_PRIMARY'] = 'true';
        $_SERVER['FIRESTORE_STUDENT_PRIMARY'] = 'true';

        // Mock Firestore scoring to simulate missing Firestore data.
        $this->app->bind(FirestorePenilaianService::class, function () {
            return new class {
                public function hitungNilai(array $jawabanPeserta, array $soalIds): array
                {
                    return [
                        'nilai_total' => 0,
                        'jumlah_benar' => 0,
                        'jumlah_salah' => 0,
                        'jumlah_soal' => 0,
                        'detail_jawaban' => [],
                    ];
                }
            };
        });

        $admin = User::factory()->create();
        $student = User::factory()->create();

        $mapel = MataPelajaran::create([
            'nama' => 'Bahasa Indonesia',
            'kode' => 'BIN',
            'deskripsi' => null,
            'is_active' => true,
        ]);

        $simulasi = Simulasi::create([
            'nama_simulasi' => 'Simulasi BIN',
            'deskripsi' => null,
            'mata_pelajaran_id' => $mapel->id,
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->addHour(),
            'durasi_menit' => 30,
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $soal = Soal::create([
            'kode_soal' => 'SOAL-010',
            'mata_pelajaran_id' => $mapel->id,
            'jenis_soal' => 'pilihan_ganda',
            'pertanyaan' => 'Ibu kota Indonesia adalah ...',
            'gambar_pertanyaan' => null,
            'jawaban_benar' => 'A',
            'bobot' => 1,
            'created_by' => $admin->id,
        ]);

        PilihanJawaban::create([
            'soal_id' => $soal->id,
            'label' => 'A',
            'teks_jawaban' => 'Jakarta',
            'gambar_jawaban' => null,
            'is_benar' => true,
        ]);

        PilihanJawaban::create([
            'soal_id' => $soal->id,
            'label' => 'B',
            'teks_jawaban' => 'Bandung',
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

        $respFinish = $this->withSession($session)->postJson('/simulasi/finish-exam', [
            'answers' => [
                $soal->id => 'A',
            ],
        ]);

        $respFinish->assertStatus(200);
        $respFinish->assertJson(['success' => true]);

        // Verify persisted nilai is computed via DB fallback.
        $nilai = Nilai::where('user_id', $student->id)
            ->where('simulasi_id', $simulasi->id)
            ->first();

        $this->assertNotNull($nilai);
        $this->assertSame(1, (int) $nilai->jumlah_soal);
        $this->assertSame(1, (int) $nilai->jumlah_benar);
        $this->assertSame(100.0, (float) $nilai->nilai_total);

        // Review page should render (uses recalculation + fallbacks).
        $respReview = $this->get('/simulasi/review');
        $respReview->assertStatus(200);
        $respReview->assertSee('Review Hasil Simulasi');
    }
}
