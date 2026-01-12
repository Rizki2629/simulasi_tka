<?php

namespace App\Services;

class FirestorePenilaianService
{
    public function __construct(private readonly FirestoreBankSoalStore $bank)
    {
    }

    /**
     * Hitung nilai dari jawaban peserta menggunakan data Firestore.
     *
     * @param array $jawabanPeserta ['soal_id' => 'jawaban']
     * @param array $soalIds array<int>
     * @return array{nilai_total:float,jumlah_benar:int,jumlah_salah:int,jumlah_soal:int,detail_jawaban:array<int,array>}
     */
    public function hitungNilai(array $jawabanPeserta, array $soalIds): array
    {
        $nilaiTotal = 0;
        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $detailJawaban = [];

        foreach ($soalIds as $soalId) {
            $soalId = (int) $soalId;
            if ($soalId <= 0) {
                continue;
            }

            $soal = $this->bank->getSoalById($soalId);
            if (!is_array($soal)) {
                continue;
            }

            // If this is a paket, score each sub-soal as a separate item.
            // UI stores answers by sub-soal id (e.g. sub_123), so scoring the paket itself would always be empty.
            if (($soal['jenis_soal'] ?? null) === 'paket') {
                $subSoals = $this->bank->listSubSoalBySoalId($soalId, 2000);
                foreach ($subSoals as $sub) {
                    $subId = (int) ($sub['id'] ?? 0);
                    if ($subId <= 0) {
                        continue;
                    }

                    $jawabanSub = $jawabanPeserta['sub_' . $subId] ?? ($jawabanPeserta[$subId] ?? null);
                    $hasilSub = $this->evaluasiSubSoal($sub, $jawabanSub);

                    $nilaiTotal += (float) ($hasilSub['nilai'] ?? 0);
                    $jumlahBenar += (int) ($hasilSub['benar'] ?? 0);
                    $jumlahSalah += (int) ($hasilSub['salah'] ?? 0);

                    $detailJawaban[] = [
                        'soal_id' => $soalId,
                        'sub_soal_id' => $subId,
                        'jenis_soal' => $sub['jenis_soal'] ?? null,
                        'jawaban_user' => $jawabanSub,
                        'jawaban_benar' => $hasilSub['kunci_jawaban'] ?? null,
                        'nilai' => (float) ($hasilSub['nilai'] ?? 0),
                        'maksimal' => (int) ($hasilSub['maksimal'] ?? 1),
                        'detail' => $hasilSub['detail'] ?? null,
                    ];
                }

                continue;
            }

            $jawabanUser = $jawabanPeserta[$soalId] ?? null;
            $hasil = $this->evaluasiJawaban($soal, $jawabanUser, $jawabanPeserta);

            $nilaiTotal += (float) ($hasil['nilai'] ?? 0);
            $jumlahBenar += (int) ($hasil['benar'] ?? 0);
            $jumlahSalah += (int) ($hasil['salah'] ?? 0);

            $detailJawaban[] = [
                'soal_id' => $soalId,
                'jenis_soal' => $soal['jenis_soal'] ?? null,
                'jawaban_user' => $jawabanUser,
                'jawaban_benar' => $hasil['kunci_jawaban'] ?? null,
                'nilai' => (float) ($hasil['nilai'] ?? 0),
                'maksimal' => (int) ($hasil['maksimal'] ?? 1),
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

    private function evaluasiSubSoal(array $sub, mixed $jawabanUser): array
    {
        $jenis = (string) ($sub['jenis_soal'] ?? '');

        return match ($jenis) {
            'pilihan_ganda' => $this->evaluasiSubPilihanGanda((int) ($sub['id'] ?? 0), $sub, $jawabanUser),
            'mcma', 'pilihan_ganda_kompleks' => $this->evaluasiSubMCMA((int) ($sub['id'] ?? 0), $jawabanUser),
            'benar_salah' => $this->evaluasiSubBenarSalah((int) ($sub['id'] ?? 0), $sub, $jawabanUser),
            'isian', 'uraian' => $this->evaluasiSubIsianUraian($sub, $jawabanUser),
            default => [
                'nilai' => 0,
                'benar' => 0,
                'salah' => 1,
                'maksimal' => 1,
                'kunci_jawaban' => null,
            ],
        };
    }

    private function evaluasiSubPilihanGanda(int $subSoalId, array $sub, mixed $jawabanUser): array
    {
        $kunciJawaban = $sub['jawaban_benar'] ?? null;

        if ($kunciJawaban === null || $kunciJawaban === '') {
            $pilihan = $this->bank->listSubPilihanJawabanBySubSoalId($subSoalId, 500);
            foreach ($pilihan as $p) {
                if (!empty($p['is_benar'])) {
                    $kunciJawaban = $p['label'] ?? null;
                    break;
                }
            }
        }

        $jawabanUserStr = is_array($jawabanUser) ? implode(',', $jawabanUser) : (string) $jawabanUser;
        $jawabanUserStr = trim($jawabanUserStr);
        $benar = ($jawabanUserStr !== '' && $kunciJawaban !== null && $jawabanUserStr == $kunciJawaban) ? 1 : 0;

        return [
            'nilai' => $benar,
            'benar' => $benar,
            'salah' => $benar ? 0 : 1,
            'maksimal' => 1,
            'kunci_jawaban' => $kunciJawaban,
        ];
    }

    private function evaluasiSubMCMA(int $subSoalId, mixed $jawabanUser): array
    {
        $pilihan = $this->bank->listSubPilihanJawabanBySubSoalId($subSoalId, 500);
        $kunci = [];
        foreach ($pilihan as $p) {
            if (!empty($p['is_benar'])) {
                $lbl = (string) ($p['label'] ?? '');
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

    private function evaluasiSubBenarSalah(int $subSoalId, array $sub, mixed $jawabanUser): array
    {
        // Complex table B/S: jawaban user is a map { optionId: 'B'/'S' or 'benar'/'salah' }
        // and keys are stored per option via is_benar.
        if (is_array($jawabanUser)) {
            $pilihan = $this->bank->listSubPilihanJawabanBySubSoalId($subSoalId, 500);
            if (!empty($pilihan)) {
                return $this->evaluasiBenarSalahByOptions($pilihan, $jawabanUser);
            }
        }

        // Prefer explicit key on sub-soal if present.
        $kunciJawaban = $sub['jawaban_benar'] ?? null;

        // If missing, infer from sub pilihan is_benar.
        if ($kunciJawaban === null || $kunciJawaban === '') {
            $pilihan = $this->bank->listSubPilihanJawabanBySubSoalId($subSoalId, 50);
            foreach ($pilihan as $p) {
                if (!empty($p['is_benar'])) {
                    $kunciJawaban = $p['label'] ?? null;
                    break;
                }
            }
        }

        $jawabanUserStr = is_array($jawabanUser) ? implode(',', $jawabanUser) : (string) $jawabanUser;
        $jawabanUserStr = trim($jawabanUserStr);

        // Support B/S variants.
        $normalizedUser = $this->normalizeBenarSalahValue($jawabanUserStr) ?? $jawabanUserStr;
        $normalizedKey = $this->normalizeBenarSalahValue($kunciJawaban) ?? $kunciJawaban;

        $benar = ($normalizedUser !== '' && $normalizedKey !== null && $normalizedUser == $normalizedKey) ? 1 : 0;

        return [
            'nilai' => $benar,
            'benar' => $benar,
            'salah' => $benar ? 0 : 1,
            'maksimal' => 1,
            'kunci_jawaban' => $normalizedKey,
        ];
    }

    private function evaluasiSubIsianUraian(array $sub, mixed $jawabanUser): array
    {
        $kunci = $sub['kunci_jawaban'] ?? ($sub['jawaban_benar'] ?? null);
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

    private function evaluasiJawaban(array $soal, mixed $jawabanUser, array $jawabanPeserta): array
    {
        $jenis = (string) ($soal['jenis_soal'] ?? '');

        return match ($jenis) {
            'pilihan_ganda' => $this->evaluasiPilihanGanda($soal, $jawabanUser),
            'benar_salah' => $this->evaluasiBenarSalah((int) ($soal['id'] ?? 0), $jawabanUser, $jawabanPeserta),
            'mcma', 'pilihan_ganda_kompleks' => $this->evaluasiMCMA((int) ($soal['id'] ?? 0), $jawabanUser, $jawabanPeserta),
            default => [
                'nilai' => 0,
                'benar' => 0,
                'salah' => 1,
                'maksimal' => 1,
                'kunci_jawaban' => null,
            ],
        };
    }

    private function evaluasiPilihanGanda(array $soal, mixed $jawabanUser): array
    {
        $kunciJawaban = $soal['jawaban_benar'] ?? null;

        // If jawaban_benar is missing, infer from pilihan_jawaban.is_benar when available.
        if ($kunciJawaban === null || $kunciJawaban === '') {
            $pilihan = $this->bank->listPilihanJawabanBySoalId((int) ($soal['id'] ?? 0), 500);
            foreach ($pilihan as $p) {
                if (!empty($p['is_benar'])) {
                    $kunciJawaban = $p['label'] ?? null;
                    break;
                }
            }
        }

        $jawabanUserStr = is_array($jawabanUser) ? implode(',', $jawabanUser) : (string) $jawabanUser;
        $jawabanUserStr = trim($jawabanUserStr);
        $benar = ($jawabanUserStr !== '' && $kunciJawaban !== null && $jawabanUserStr == $kunciJawaban) ? 1 : 0;

        return [
            'nilai' => $benar,
            'benar' => $benar,
            'salah' => $benar ? 0 : 1,
            'maksimal' => 1,
            'kunci_jawaban' => $kunciJawaban,
        ];
    }

    private function evaluasiBenarSalah(int $soalId, mixed $jawabanUser, array $jawabanPeserta): array
    {
        $subSoals = $this->bank->listSubSoalBySoalId($soalId, 1000);
        $jumlahSub = count($subSoals);

        // If no sub-soal, some banks store B/S statements as pilihan_jawaban rows.
        if ($jumlahSub === 0) {
            $pilihan = $this->bank->listPilihanJawabanBySoalId($soalId, 500);
            if (!empty($pilihan)) {
                return $this->evaluasiBenarSalahByOptions($pilihan, $jawabanUser);
            }

            return [
                'nilai' => 0,
                'benar' => 0,
                'salah' => 1,
                'maksimal' => 1,
                'kunci_jawaban' => null,
                'detail' => [],
            ];
        }

        // Normalize user answers.
        // Preferred formats seen in UI:
        // - per sub key: jawabanPeserta['sub_123'] = 'benar'/'salah' or 'B'/'S'
        // - legacy parent key string: jawabanPeserta[soalId] = "123:B,124:S"
        $jawabanArray = $this->parseJawabanPairs((string) $jawabanUser);

        if (empty($jawabanArray)) {
            foreach ($subSoals as $sub) {
                $subId = (int) ($sub['id'] ?? 0);
                if ($subId <= 0) {
                    continue;
                }
                $key1 = 'sub_' . $subId;
                $val = $jawabanPeserta[$key1] ?? ($jawabanPeserta[$subId] ?? null);
                if ($val === null) {
                    continue;
                }
                $jawabanArray[$subId] = $this->normalizeBenarSalahValue($val);
            }
        }

        $nilaiTotal = 0;
        $jumlahBenar = 0;
        $detail = [];
        $kunciJawaban = [];

        foreach ($subSoals as $sub) {
            $subId = (int) ($sub['id'] ?? 0);
            if ($subId <= 0) {
                continue;
            }

            // Complex table B/S stored as sub pilihan rows with is_benar.
            // UI stores answers as associative map: answers['sub_123'][optionId] = 'B'/'S'.
            $key1 = 'sub_' . $subId;
            $rawUser = $jawabanPeserta[$key1] ?? ($jawabanPeserta[$subId] ?? null);
            if (is_array($rawUser)) {
                $subPilihan = $this->bank->listSubPilihanJawabanBySubSoalId($subId, 500);
                if (!empty($subPilihan)) {
                    $hasilOpt = $this->evaluasiBenarSalahByOptions($subPilihan, $rawUser);
                    $nilaiTotal += (int) ($hasilOpt['nilai'] ?? 0);
                    $jumlahBenar += (int) ($hasilOpt['benar'] ?? 0);
                    $jumlahSalah += (int) ($hasilOpt['salah'] ?? 0);

                    $detail[] = [
                        'sub_soal_id' => $subId,
                        'jawaban_user' => $rawUser,
                        'jawaban_benar' => $hasilOpt['kunci_jawaban'] ?? null,
                        'benar' => (int) ($hasilOpt['benar'] ?? 0),
                        'detail' => $hasilOpt['detail'] ?? null,
                    ];
                    $kunciJawaban[$subId] = $hasilOpt['kunci_jawaban'] ?? null;
                    continue;
                }
            }

            // Simple per-sub B/S: expected from sub.jawaban_benar
            $kunci = $this->normalizeBenarSalahValue($sub['jawaban_benar'] ?? null);
            $jawabanSub = $jawabanArray[$subId] ?? $this->normalizeBenarSalahValue($rawUser);

            $benar = ($jawabanSub !== null && $kunci !== null && $jawabanSub == $kunci) ? 1 : 0;
            $nilaiTotal += $benar;
            $jumlahBenar += $benar;
            $jumlahSalah += ($benar ? 0 : 1);

            $detail[] = [
                'sub_soal_id' => $subId,
                'jawaban_user' => $jawabanSub,
                'jawaban_benar' => $kunci,
                'benar' => $benar,
            ];
            $kunciJawaban[$subId] = $kunci;
        }

        return [
            'nilai' => $nilaiTotal,
            'benar' => $jumlahBenar,
            'salah' => $jumlahSalah,
            'maksimal' => $jumlahSub,
            'kunci_jawaban' => $kunciJawaban,
            'detail' => $detail,
        ];
    }

    /**
     * Score Benar/Salah stored as per-option rows with boolean correctness.
     *
     * @param array<int, array> $options Each option must contain at least 'id' and 'is_benar'
     * @return array{nilai:int, benar:int, salah:int, maksimal:int, kunci_jawaban:array<string,string>, detail:array<int,array>}
     */
    private function evaluasiBenarSalahByOptions(array $options, mixed $jawabanUser): array
    {
        $userMap = is_array($jawabanUser) ? $jawabanUser : [];

        $nilai = 0;
        $benar = 0;
        $salah = 0;
        $maks = 0;
        $kunci = [];
        $detail = [];

        foreach ($options as $opt) {
            $optId = (string) ($opt['id'] ?? '');
            if ($optId === '') {
                continue;
            }

            $expected = !empty($opt['is_benar']) ? 'B' : 'S';
            $kunci[$optId] = $expected;
            $maks++;

            $rawUser = $userMap[$optId] ?? null;
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

    private function evaluasiMCMA(int $soalId, mixed $jawabanUser, array $jawabanPeserta): array
    {
        // UI common format for MCMA is a comma-separated list of selected labels: "A,B".
        // We'll score by pilihan_jawaban.is_benar when available.
        $pilihan = $this->bank->listPilihanJawabanBySoalId($soalId, 500);
        $kunci = [];
        foreach ($pilihan as $p) {
            if (!empty($p['is_benar'])) {
                $lbl = (string) ($p['label'] ?? '');
                if ($lbl !== '') {
                    $kunci[] = $lbl;
                }
            }
        }

        $selected = $this->parseCommaList($jawabanUser);
        // Support cases where answers were saved per-sub under keys like sub_123 with value "A".
        // If parent is empty and there are sub_soal entries, try pair format or per-sub keys.
        if (empty($selected)) {
            $subSoals = $this->bank->listSubSoalBySoalId($soalId, 1000);
            $pairs = $this->parseJawabanPairs(is_array($jawabanUser) ? '' : (string) $jawabanUser);
            foreach ($subSoals as $sub) {
                $subId = (int) ($sub['id'] ?? 0);
                if ($subId <= 0) {
                    continue;
                }
                $key1 = 'sub_' . $subId;
                $val = $pairs[$subId] ?? ($jawabanPeserta[$key1] ?? ($jawabanPeserta[$subId] ?? null));
                if ($val === null) {
                    continue;
                }
                $valStr = trim((string) (is_array($val) ? implode(',', $val) : $val));
                if ($valStr !== '') {
                    // For packet-style checkbox, value might be "1"; treat it as selecting the sub label if exists.
                    // If value is a letter/label, use it as selection.
                    if ($valStr !== '1') {
                        $selected[] = $valStr;
                    }
                }
            }
        }

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

    private function normalizeBenarSalahValue(mixed $val): ?string
    {
        if ($val === null) {
            return null;
        }
        if (is_array($val)) {
            // Not a supported shape for scoring.
            return null;
        }
        $s = strtolower(trim((string) $val));
        if ($s === '') {
            return null;
        }
        // Accept both UI variants and compact codes.
        if ($s === 'benar') {
            return 'B';
        }
        if ($s === 'salah') {
            return 'S';
        }
        if ($s === 'b') {
            return 'B';
        }
        if ($s === 's') {
            return 'S';
        }
        return strtoupper($s);
    }

    /**
     * Parse "A,B,C" or array to list of tokens.
     *
     * @return array<int, string>
     */
    private function parseCommaList(mixed $val): array
    {
        if ($val === null) {
            return [];
        }
        if (is_array($val)) {
            return array_values(array_filter(array_map(fn ($x) => trim((string) $x), $val), fn ($x) => $x !== ''));
        }
        $s = trim((string) $val);
        if ($s === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $s)), fn ($x) => $x !== ''));
    }

    /**
     * Parse string format "subId:Answer,subId:Answer".
     *
     * @return array<int, string>
     */
    private function parseJawabanPairs(string $jawabanString): array
    {
        $jawabanString = trim($jawabanString);
        if ($jawabanString === '') {
            return [];
        }

        $result = [];
        foreach (explode(',', $jawabanString) as $item) {
            $parts = explode(':', $item);
            if (count($parts) === 2) {
                $sid = (int) trim($parts[0]);
                if ($sid > 0) {
                    $result[$sid] = trim($parts[1]);
                }
            }
        }

        return $result;
    }
}
