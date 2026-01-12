<?php

namespace Tests\Unit;

use App\Services\FirestoreBankSoalStore;
use App\Services\FirestorePenilaianService;
use PHPUnit\Framework\TestCase;

class FirestorePenilaianServiceTest extends TestCase
{
    public function testPaketIsScoredPerSubSoal(): void
    {
        $bank = $this->createMock(FirestoreBankSoalStore::class);

        $bank->method('getSoalById')->willReturnCallback(function (int $id): ?array {
            if ($id === 10) {
                return ['id' => 10, 'jenis_soal' => 'paket'];
            }
            return null;
        });

        $bank->method('listSubSoalBySoalId')->willReturnCallback(function (int $soalId, int $limit = 500): array {
            if ($soalId !== 10) {
                return [];
            }

            return [
                ['id' => 101, 'soal_id' => 10, 'nomor_urut' => 1, 'jenis_soal' => 'pilihan_ganda', 'jawaban_benar' => 'A'],
                ['id' => 102, 'soal_id' => 10, 'nomor_urut' => 2, 'jenis_soal' => 'pilihan_ganda', 'jawaban_benar' => 'B'],
            ];
        });

        $bank->method('listSubPilihanJawabanBySubSoalId')->willReturn([]);
        $bank->method('listPilihanJawabanBySoalId')->willReturn([]);

        $service = new FirestorePenilaianService($bank);

        $hasil = $service->hitungNilai([
            'sub_101' => 'A',
            'sub_102' => 'C',
        ], [10]);

        $this->assertSame(2, $hasil['jumlah_soal']);
        $this->assertSame(1, $hasil['jumlah_benar']);
        $this->assertSame(1, $hasil['jumlah_salah']);
        $this->assertSame(1.0, $hasil['nilai_total']);

        $this->assertCount(2, $hasil['detail_jawaban']);
        $this->assertSame(10, $hasil['detail_jawaban'][0]['soal_id']);
        $this->assertSame(101, $hasil['detail_jawaban'][0]['sub_soal_id']);
    }
}
