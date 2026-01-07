<?php

namespace App\Services;

use App\Models\Soal;
use App\Models\SubSoal;
use App\Models\PilihanJawaban;
use App\Models\SubPilihanJawaban;

class PenilaianService
{
    /**
     * Hitung nilai dari jawaban peserta
     * 
     * @param array $jawabanPeserta Array dengan format ['soal_id' => 'jawaban']
     * @param array $soalIds Array dari ID soal yang dikerjakan
     * @return array [nilai_total, jumlah_benar, jumlah_salah, detail_jawaban]
     */
    public function hitungNilai($jawabanPeserta, $soalIds)
    {
        $nilaiTotal = 0;
        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $detailJawaban = [];

        $soalList = Soal::with(['pilihanJawaban', 'subSoal.pilihanJawaban'])
                        ->whereIn('id', $soalIds)
                        ->get();

        foreach ($soalList as $soal) {
            $soalId = $soal->id;
            $jawabanUser = $jawabanPeserta[$soalId] ?? null;

            $hasil = $this->evaluasiJawaban($soal, $jawabanUser);
            
            $nilaiTotal += $hasil['nilai'];
            $jumlahBenar += $hasil['benar'];
            $jumlahSalah += $hasil['salah'];

            $detailJawaban[] = [
                'soal_id' => $soalId,
                'jenis_soal' => $soal->jenis_soal,
                'jawaban_user' => $jawabanUser,
                'jawaban_benar' => $hasil['kunci_jawaban'],
                'nilai' => $hasil['nilai'],
                'maksimal' => $hasil['maksimal'],
                'detail' => $hasil['detail'] ?? null,
            ];
        }

        return [
            'nilai_total' => $nilaiTotal,
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'jumlah_soal' => count($soalList),
            'detail_jawaban' => $detailJawaban,
        ];
    }

    /**
     * Evaluasi jawaban untuk satu soal
     */
    private function evaluasiJawaban($soal, $jawabanUser)
    {
        switch ($soal->jenis_soal) {
            case 'pilihan_ganda':
                return $this->evaluasiPilihanGanda($soal, $jawabanUser);
            
            case 'benar_salah':
                return $this->evaluasiBenarSalah($soal, $jawabanUser);
            
            case 'mcma':
                return $this->evaluasiMCMA($soal, $jawabanUser);
            
            default:
                return [
                    'nilai' => 0,
                    'benar' => 0,
                    'salah' => 1,
                    'maksimal' => 1,
                    'kunci_jawaban' => null,
                ];
        }
    }

    /**
     * Evaluasi Pilihan Ganda
     * Jawaban benar = 1 poin
     */
    private function evaluasiPilihanGanda($soal, $jawabanUser)
    {
        $kunciJawaban = $soal->jawaban_benar;
        $benar = ($jawabanUser == $kunciJawaban) ? 1 : 0;

        return [
            'nilai' => $benar,
            'benar' => $benar,
            'salah' => $benar ? 0 : 1,
            'maksimal' => 1,
            'kunci_jawaban' => $kunciJawaban,
        ];
    }

    /**
     * Evaluasi Benar Salah
     * Setiap pernyataan benar = 1 poin
     */
    private function evaluasiBenarSalah($soal, $jawabanUser)
    {
        $subSoals = $soal->subSoal;
        $jumlahSubSoal = $subSoals->count();
        
        if ($jumlahSubSoal == 0) {
            return [
                'nilai' => 0,
                'benar' => 0,
                'salah' => 1,
                'maksimal' => 1,
                'kunci_jawaban' => null,
                'detail' => [],
            ];
        }

        // Parse jawaban user (format: "1:B,2:S,3:B,4:S")
        $jawabanArray = $this->parseJawabanBenarSalah($jawabanUser);
        
        $nilaiTotal = 0;
        $jumlahBenar = 0;
        $detail = [];
        $kunciJawaban = [];

        foreach ($subSoals as $subSoal) {
            $subSoalId = $subSoal->id;
            $kunciSubSoal = $subSoal->jawaban_benar; // 'B' atau 'S'
            $jawabanSubUser = $jawabanArray[$subSoalId] ?? null;

            $benar = ($jawabanSubUser == $kunciSubSoal) ? 1 : 0;
            $nilaiTotal += $benar;
            $jumlahBenar += $benar;

            $detail[] = [
                'sub_soal_id' => $subSoalId,
                'jawaban_user' => $jawabanSubUser,
                'jawaban_benar' => $kunciSubSoal,
                'benar' => $benar,
            ];

            $kunciJawaban[$subSoalId] = $kunciSubSoal;
        }

        return [
            'nilai' => $nilaiTotal,
            'benar' => $jumlahBenar,
            'salah' => $jumlahSubSoal - $jumlahBenar,
            'maksimal' => $jumlahSubSoal,
            'kunci_jawaban' => $kunciJawaban,
            'detail' => $detail,
        ];
    }

    /**
     * Evaluasi MCMA (Multiple Choice Multiple Answer)
     * Setiap pilihan benar = 1 poin
     */
    private function evaluasiMCMA($soal, $jawabanUser)
    {
        $subSoals = $soal->subSoal;
        $jumlahSubSoal = $subSoals->count();
        
        if ($jumlahSubSoal == 0) {
            return [
                'nilai' => 0,
                'benar' => 0,
                'salah' => 1,
                'maksimal' => 1,
                'kunci_jawaban' => null,
                'detail' => [],
            ];
        }

        // Parse jawaban user (format: "1:A,2:B,3:C")
        $jawabanArray = $this->parseJawabanMCMA($jawabanUser);
        
        $nilaiTotal = 0;
        $jumlahBenar = 0;
        $detail = [];
        $kunciJawaban = [];

        foreach ($subSoals as $subSoal) {
            $subSoalId = $subSoal->id;
            $kunciSubSoal = $subSoal->jawaban_benar; // ID pilihan jawaban atau label
            $jawabanSubUser = $jawabanArray[$subSoalId] ?? null;

            $benar = ($jawabanSubUser == $kunciSubSoal) ? 1 : 0;
            $nilaiTotal += $benar;
            $jumlahBenar += $benar;

            $detail[] = [
                'sub_soal_id' => $subSoalId,
                'jawaban_user' => $jawabanSubUser,
                'jawaban_benar' => $kunciSubSoal,
                'benar' => $benar,
            ];

            $kunciJawaban[$subSoalId] = $kunciSubSoal;
        }

        return [
            'nilai' => $nilaiTotal,
            'benar' => $jumlahBenar,
            'salah' => $jumlahSubSoal - $jumlahBenar,
            'maksimal' => $jumlahSubSoal,
            'kunci_jawaban' => $kunciJawaban,
            'detail' => $detail,
        ];
    }

    /**
     * Parse jawaban Benar Salah dari string
     * Format: "1:B,2:S,3:B" -> [1 => 'B', 2 => 'S', 3 => 'B']
     */
    private function parseJawabanBenarSalah($jawabanString)
    {
        if (empty($jawabanString)) {
            return [];
        }

        $result = [];
        $items = explode(',', $jawabanString);
        
        foreach ($items as $item) {
            $parts = explode(':', $item);
            if (count($parts) == 2) {
                $result[(int)$parts[0]] = $parts[1];
            }
        }

        return $result;
    }

    /**
     * Parse jawaban MCMA dari string
     * Format: "1:A,2:B,3:C" -> [1 => 'A', 2 => 'B', 3 => 'C']
     */
    private function parseJawabanMCMA($jawabanString)
    {
        if (empty($jawabanString)) {
            return [];
        }

        $result = [];
        $items = explode(',', $jawabanString);
        
        foreach ($items as $item) {
            $parts = explode(':', $item);
            if (count($parts) == 2) {
                $result[(int)$parts[0]] = $parts[1];
            }
        }

        return $result;
    }
}
