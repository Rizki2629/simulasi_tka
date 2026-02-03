<?php

namespace App\Services;

class FirestoreSimulasiSoalStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
    }

    /**
     * @return array<int, array{soal_id:int,urutan:int}>
     */
    public function listSoalRefsBySimulasiId(int $simulasiId, int $limit = 2000): array
    {
        // Avoid composite indexes by querying a single field.
        $rows = $this->client->runQueryEquals('simulasi_soal', [
            'simulasi_id' => $simulasiId,
        ], $limit);

        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            $sid = (int) ($data['soal_id'] ?? 0);
            if ($sid <= 0) {
                continue;
            }
            $out[] = [
                'soal_id' => $sid,
                'urutan' => (int) ($data['urutan'] ?? 0),
            ];
        }

        usort($out, fn ($a, $b) => ($a['urutan'] <=> $b['urutan']) ?: ($a['soal_id'] <=> $b['soal_id']));
        return $out;
    }

    public function deleteAllBySimulasiId(int $simulasiId, int $limit = 5000): int
    {
        $rows = $this->client->runQueryEquals('simulasi_soal', [
            'simulasi_id' => $simulasiId,
        ], $limit);

        $deleted = 0;
        foreach ($rows as $row) {
            $docId = $row['docId'] ?? null;
            if (is_string($docId) && $docId !== '') {
                $this->client->deleteDocument('simulasi_soal', $docId);
                $deleted++;
            }
        }

        return $deleted;
    }
}
