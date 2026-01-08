<?php

namespace App\Services;

use Carbon\Carbon;
use Google\Cloud\Core\Timestamp;
use Illuminate\Support\Collection;

class SoalViewAdapter
{
    /**
     * @param array<int, array> $rows
     */
    public function hydrateSoalList(array $rows): Collection
    {
        return collect($rows)->map(function ($row) {
            return $this->hydrateSoal($row);
        });
    }

    public function hydrateSoal(array $data): object
    {
        $mataPelajaranNama = (string) ($data['mata_pelajaran_nama'] ?? $data['mataPelajaran']['nama'] ?? '');
        $creatorName = (string) ($data['created_by_name'] ?? $data['creator']['name'] ?? 'Admin');

        $pilihan = $this->hydratePilihanJawabanList($data['pilihan_jawaban'] ?? $data['pilihanJawaban'] ?? []);
        $subSoal = $this->hydrateSubSoalList($data['sub_soal'] ?? $data['subSoal'] ?? []);

        return (object) [
            'id' => (int) ($data['id'] ?? 0),
            'kode_soal' => (string) ($data['kode_soal'] ?? ''),
            'mata_pelajaran_id' => $data['mata_pelajaran_id'] ?? null,
            'jenis_soal' => (string) ($data['jenis_soal'] ?? 'paket'),
            'pertanyaan' => (string) ($data['pertanyaan'] ?? ''),
            'pembahasan' => $data['pembahasan'] ?? null,
            'gambar_pertanyaan' => $data['gambar_pertanyaan'] ?? null,
            'gambar_pembahasan' => $data['gambar_pembahasan'] ?? null,
            'jawaban_benar' => $data['jawaban_benar'] ?? null,
            'kunci_jawaban' => $data['kunci_jawaban'] ?? null,
            'bobot' => (int) ($data['bobot'] ?? 0),
            'created_by' => $data['created_by'] ?? null,
            'created_at' => $this->asCarbon($data['created_at'] ?? null),
            'updated_at' => $this->asCarbon($data['updated_at'] ?? null),

            'mataPelajaran' => (object) [
                'nama' => $mataPelajaranNama ?: '-'
            ],
            'creator' => (object) [
                'name' => $creatorName ?: 'Admin'
            ],

            'simulasi_soal_count' => (int) ($data['simulasi_soal_count'] ?? 0),

            // Blade uses these as relationships/collections
            'pilihanJawaban' => $pilihan,
            'subSoal' => $subSoal,
        ];
    }

    /**
     * @param array<int, array>|array<int, object> $rows
     */
    private function hydrateSubSoalList(array $rows): Collection
    {
        return collect($rows)->map(function ($row) {
            $rowArr = is_object($row) ? (array) $row : (array) $row;

            $pilihanRaw = $rowArr['pilihan_jawaban'] ?? $rowArr['pilihanJawaban'] ?? [];
            $pilihan = $this->hydratePilihanJawabanList(is_array($pilihanRaw) ? $pilihanRaw : []);

            // JSON in edit.blade.php expects pilihan_jawaban field
            $pilihanJson = $pilihan->map(function ($p) {
                return [
                    'id' => $p->id,
                    'label' => $p->label,
                    'teks_jawaban' => $p->teks_jawaban,
                    'gambar_jawaban' => $p->gambar_jawaban,
                    'is_benar' => $p->is_benar,
                ];
            })->values()->all();

            return (object) [
                'id' => (int) ($rowArr['id'] ?? 0),
                'nomor_urut' => (int) ($rowArr['nomor_urut'] ?? 0),
                'jenis_soal' => (string) ($rowArr['jenis_soal'] ?? ''),
                'pertanyaan' => (string) ($rowArr['pertanyaan'] ?? ''),
                'pembahasan' => $rowArr['pembahasan'] ?? null,
                'gambar_pertanyaan' => $rowArr['gambar_pertanyaan'] ?? null,
                'jawaban_benar' => $rowArr['jawaban_benar'] ?? null,
                'kunci_jawaban' => $rowArr['kunci_jawaban'] ?? null,
                'created_at' => $this->asCarbon($rowArr['created_at'] ?? null),
                'updated_at' => $this->asCarbon($rowArr['updated_at'] ?? null),

                // Blade
                'pilihanJawaban' => $pilihan,

                // JS (edit) expects snake_case
                'pilihan_jawaban' => $pilihanJson,
            ];
        });
    }

    /**
     * @param array<int, array>|array<int, object> $rows
     */
    private function hydratePilihanJawabanList(array $rows): Collection
    {
        return collect($rows)->map(function ($row) {
            $rowArr = is_object($row) ? (array) $row : (array) $row;

            return (object) [
                'id' => (int) ($rowArr['id'] ?? 0),
                'label' => (string) ($rowArr['label'] ?? ''),
                'teks_jawaban' => (string) ($rowArr['teks_jawaban'] ?? ''),
                'gambar_jawaban' => $rowArr['gambar_jawaban'] ?? null,
                'is_benar' => (bool) ($rowArr['is_benar'] ?? false),
            ];
        });
    }

    private function asCarbon(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof Timestamp) {
            return Carbon::instance($value->get());
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                // fall through
            }
        }

        return now();
    }
}
