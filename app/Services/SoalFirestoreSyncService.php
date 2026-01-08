<?php

namespace App\Services;

use App\Models\Soal;

class SoalFirestoreSyncService
{
    public function __construct(
        private readonly SoalFirestoreRepository $repo,
    ) {
    }

    public function sync(Soal $soal): void
    {
        $soal->loadMissing([
            'mataPelajaran',
            'creator',
            'subSoal.pilihanJawaban',
            'pilihanJawaban',
        ]);

        $payload = [
            'kode_soal' => $soal->kode_soal,
            'mata_pelajaran_id' => $soal->mata_pelajaran_id,
            'mata_pelajaran_nama' => $soal->mataPelajaran->nama ?? null,
            'jenis_soal' => $soal->jenis_soal,
            'pertanyaan' => $soal->pertanyaan,
            'pembahasan' => $soal->pembahasan,
            'gambar_pertanyaan' => $soal->gambar_pertanyaan,
            'bobot' => (int) ($soal->bobot ?? 0),
            'jawaban_benar' => $soal->jawaban_benar,
            'created_by' => $soal->created_by,
            'created_by_name' => $soal->creator->name ?? 'Admin',
            'simulasi_soal_count' => $soal->simulasiSoal()->count(),
            'created_at' => $soal->created_at,
            'updated_at' => $soal->updated_at,
            'pilihan_jawaban' => $soal->pilihanJawaban->map(function ($pil) {
                return [
                    'id' => (int) $pil->id,
                    'label' => $pil->label,
                    'teks_jawaban' => $pil->teks_jawaban,
                    'gambar_jawaban' => $pil->gambar_jawaban,
                    'is_benar' => (bool) $pil->is_benar,
                ];
            })->values()->all(),
            'sub_soal' => $soal->subSoal->map(function ($sub) {
                return [
                    'id' => (int) $sub->id,
                    'nomor_urut' => (int) $sub->nomor_urut,
                    'jenis_soal' => $sub->jenis_soal,
                    'pertanyaan' => $sub->pertanyaan,
                    'pembahasan' => $sub->pembahasan,
                    'gambar_pertanyaan' => $sub->gambar_pertanyaan,
                    'jawaban_benar' => $sub->jawaban_benar,
                    'kunci_jawaban' => $sub->kunci_jawaban,
                    'created_at' => $sub->created_at,
                    'updated_at' => $sub->updated_at,
                    'pilihan_jawaban' => $sub->pilihanJawaban->map(function ($pil) {
                        return [
                            'id' => (int) $pil->id,
                            'label' => $pil->label,
                            'teks_jawaban' => $pil->teks_jawaban,
                            'gambar_jawaban' => $pil->gambar_jawaban,
                            'is_benar' => (bool) $pil->is_benar,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];

        $this->repo->upsertWithId((int) $soal->id, $payload);
    }
}
