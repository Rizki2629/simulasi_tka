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

            // Paket/sub-soal: UI menyimpan jawaban per sub (key: sub_{id}).
            // Supaya konsisten lintas mode (Firestore/SQLite), nilai paket dihitung per sub-soal.
            if ($soal->subSoal && $soal->subSoal->count() > 0) {
                foreach ($soal->subSoal as $sub) {
                    $subId = $sub->id;
                    $jawabanSub = $jawabanPeserta['sub_' . $subId] ?? ($jawabanPeserta[$subId] ?? null);

                    $hasilSub = $this->evaluasiSubSoal($sub, $jawabanSub);

                    $nilaiTotal += $hasilSub['nilai'];
                    $jumlahBenar += $hasilSub['benar'];
                    $jumlahSalah += $hasilSub['salah'];

                    $detailJawaban[] = [
                        'soal_id' => $soalId,
                        'sub_soal_id' => $subId,
                        'jenis_soal' => $sub->jenis_soal,
                        'jawaban_user' => $jawabanSub,
                        'jawaban_benar' => $hasilSub['kunci_jawaban'],
                        'nilai' => $hasilSub['nilai'],
                        'maksimal' => $hasilSub['maksimal'],
                        'detail' => $hasilSub['detail'] ?? null,
                    ];
                }
                continue;
            }

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
            'jumlah_soal' => count($detailJawaban),
            'detail_jawaban' => $detailJawaban,
        ];
    }

    private function evaluasiSubSoal(SubSoal $subSoal, $jawabanUser): array
    {
        $jenis = (string) ($subSoal->jenis_soal ?? '');

        return match ($jenis) {
            'pilihan_ganda' => $this->evaluasiSubPilihanGanda($subSoal, $jawabanUser),
            'mcma', 'pilihan_ganda_kompleks' => $this->evaluasiSubMCMA($subSoal, $jawabanUser),
            'benar_salah' => $this->evaluasiSubBenarSalah($subSoal, $jawabanUser),
            'isian', 'uraian' => $this->evaluasiSubIsianUraian($subSoal, $jawabanUser),
            default => [
                'nilai' => 0,
                'benar' => 0,
                'salah' => 1,
                'maksimal' => 1,
                'kunci_jawaban' => null,
            ],
        };
    }

    private function evaluasiSubPilihanGanda(SubSoal $subSoal, $jawabanUser): array
    {
        $kunciJawaban = $subSoal->jawaban_benar;

        if (empty($kunciJawaban)) {
            foreach ($subSoal->pilihanJawaban as $p) {
                if ($p->is_benar) {
                    $kunciJawaban = $p->label;
                    break;
                }
            }
        }

        $jawabanUserStr = is_array($jawabanUser) ? implode(',', $jawabanUser) : (string) $jawabanUser;
        $jawabanUserStr = trim($jawabanUserStr);
        $benar = ($jawabanUserStr !== '' && !empty($kunciJawaban) && $jawabanUserStr == $kunciJawaban) ? 1 : 0;

        return [
            'nilai' => $benar,
            'benar' => $benar,
            'salah' => $benar ? 0 : 1,
            'maksimal' => 1,
            'kunci_jawaban' => $kunciJawaban,
        ];
    }

    private function evaluasiSubMCMA(SubSoal $subSoal, $jawabanUser): array
    {
        $kunci = [];
        foreach ($subSoal->pilihanJawaban as $p) {
            if ($p->is_benar) {
                $lbl = (string) ($p->label ?? '');
                if ($lbl !== '') {
                    $kunci[] = $lbl;
                }
            }
        }

        $selected = $this->parseCommaList($jawabanUser);
        $kunciSet = array_values(array_unique($kunci));
        $selectedSet = array_values(array_unique($selected));

        $benarCount = 0;
        foreach ($kunciSet as $lbl) {
            if (in_array($lbl, $selectedSet, true)) {
                $benarCount++;
            }
        }

        return [
            'nilai' => $benarCount,
            'benar' => $benarCount,
            'salah' => max(0, count($kunciSet) - $benarCount),
            'maksimal' => max(1, count($kunciSet)),
            'kunci_jawaban' => $kunciSet,
            'detail' => [
                'selected' => $selectedSet,
            ],
        ];
    }

    private function evaluasiSubBenarSalah(SubSoal $subSoal, $jawabanUser): array
    {
        // Complex table B/S: UI submits map { optionId: 'B'/'S' or 'benar'/'salah' }
        // and kunci is stored per option via is_benar.
        if (is_array($jawabanUser) && ($subSoal->pilihanJawaban?->count() ?? 0) > 0) {
            return $this->evaluasiBenarSalahByOptions($subSoal->pilihanJawaban->all(), $jawabanUser);
        }

        $kunciJawaban = $subSoal->jawaban_benar;

        if (empty($kunciJawaban)) {
            foreach ($subSoal->pilihanJawaban as $p) {
                if ($p->is_benar) {
                    $kunciJawaban = $p->label;
                    break;
                }
            }
        }

        $jawabanUserStr = is_array($jawabanUser) ? implode(',', $jawabanUser) : (string) $jawabanUser;
        $jawabanUserStr = trim($jawabanUserStr);

        $normalizedUser = $this->normalizeBenarSalahValue($jawabanUserStr) ?? $jawabanUserStr;
        $normalizedKey = $this->normalizeBenarSalahValue($kunciJawaban) ?? $kunciJawaban;

        $benar = ($normalizedUser !== '' && !empty($normalizedKey) && $normalizedUser == $normalizedKey) ? 1 : 0;

        return [
            'nilai' => $benar,
            'benar' => $benar,
            'salah' => $benar ? 0 : 1,
            'maksimal' => 1,
            'kunci_jawaban' => $normalizedKey,
        ];
    }

    private function evaluasiSubIsianUraian(SubSoal $subSoal, $jawabanUser): array
    {
        $kunci = $subSoal->kunci_jawaban ?: $subSoal->jawaban_benar;
        $user = is_array($jawabanUser) ? implode(',', $jawabanUser) : (string) $jawabanUser;

        $kunciNorm = strtolower(trim((string) $kunci));
        $userNorm = strtolower(trim($user));

        $benar = ($kunciNorm !== '' && $userNorm !== '' && $userNorm === $kunciNorm) ? 1 : 0;

        return [
            'nilai' => $benar,
            'benar' => $benar,
            'salah' => $benar ? 0 : 1,
            'maksimal' => 1,
            'kunci_jawaban' => $kunci,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseCommaList($value): array
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $v) {
                $v = trim((string) $v);
                if ($v !== '') {
                    $out[] = $v;
                }
            }
            return $out;
        }

        $s = trim((string) $value);
        if ($s === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $s));
        return array_values(array_filter($parts, fn ($p) => $p !== ''));
    }

    private function normalizeBenarSalahValue($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = strtolower(trim((string) $value));
        if ($v === '') {
            return null;
        }

        if (in_array($v, ['b', 'benar', 'true', '1', 'ya', 'y'], true)) {
            return 'B';
        }
        if (in_array($v, ['s', 'salah', 'false', '0', 'tidak', 't', 'n', 'no'], true)) {
            return 'S';
        }

        // Unknown variant
        return null;
    }

    /**
     * @param array<int, \App\Models\PilihanJawaban|\App\Models\SubPilihanJawaban> $options
     * @param array $jawabanUser map[optionId] = value
     */
    private function evaluasiBenarSalahByOptions(array $options, array $jawabanUser): array
    {
        $nilai = 0;
        $benar = 0;
        $salah = 0;
        $maks = 0;
        $kunci = [];
        $detail = [];

        foreach ($options as $opt) {
            $optId = (string) ($opt->id ?? '');
            if ($optId === '') {
                continue;
            }
            $expected = ($opt->is_benar ?? false) ? 'B' : 'S';
            $kunci[$optId] = $expected;
            $maks++;

            $rawUser = $jawabanUser[$optId] ?? null;
            $userNorm = $this->normalizeBenarSalahValue($rawUser);
            $isCorrect = ($userNorm !== null && $userNorm === $expected);

            if ($isCorrect) {
                $nilai++;
                $benar++;
            } else {
                $salah++;
            }

            $detail[] = [
                'option_id' => $optId,
                'jawaban_user' => $userNorm,
                'jawaban_benar' => $expected,
                'benar' => $isCorrect ? 1 : 0,
            ];
        }

        if ($maks <= 0) {
            $maks = 1;
            $salah = 1;
        }

        return [
            'nilai' => $nilai,
            'benar' => $benar,
            'salah' => $salah,
            'maksimal' => $maks,
            'kunci_jawaban' => $kunci,
            'detail' => $detail,
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
        // Parent-level complex B/S: statements are pilihan_jawaban rows.
        // UI submits answers[soalId] = { optionId: 'benar'/'salah' } (or variants).
        if (($soal->subSoal?->count() ?? 0) === 0 && ($soal->pilihanJawaban?->count() ?? 0) > 0 && is_array($jawabanUser)) {
            return $this->evaluasiBenarSalahByOptions($soal->pilihanJawaban->all(), $jawabanUser);
        }

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
