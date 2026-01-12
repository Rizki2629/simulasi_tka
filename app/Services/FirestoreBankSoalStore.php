<?php

namespace App\Services;

class FirestoreBankSoalStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
    }

    public function getSoalById(int $id): ?array
    {
        $rows = $this->client->runQueryEquals('soal', ['id' => $id], 1);
        return $rows[0]['data'] ?? null;
    }

    /**
     * @return array<int, array>
     */
    public function listPilihanJawabanBySoalId(int $soalId, int $limit = 200): array
    {
        $rows = $this->client->runQueryEquals('pilihan_jawaban', ['soal_id' => $soalId], $limit);
        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            $out[] = $data;
        }

        usort($out, function (array $a, array $b) {
            return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });

        return $out;
    }

    /**
     * @return array<int, array>
     */
    public function listSubSoalBySoalId(int $soalId, int $limit = 500): array
    {
        $rows = $this->client->runQueryEquals('sub_soal', ['soal_id' => $soalId], $limit);
        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            $out[] = $data;
        }

        usort($out, fn (array $a, array $b) => ((int) ($a['nomor_urut'] ?? 0)) <=> ((int) ($b['nomor_urut'] ?? 0)));
        return $out;
    }

    /**
     * @return array<int, array>
     */
    public function listSubPilihanJawabanBySubSoalId(int $subSoalId, int $limit = 200): array
    {
        $rows = $this->client->runQueryEquals('sub_pilihan_jawaban', ['sub_soal_id' => $subSoalId], $limit);
        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            $out[] = $data;
        }

        usort($out, function (array $a, array $b) {
            return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });

        return $out;
    }
}
