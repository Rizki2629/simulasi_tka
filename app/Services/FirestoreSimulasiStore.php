<?php

namespace App\Services;

class FirestoreSimulasiStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
    }

    public function getById(int $id): ?array
    {
        $rows = $this->client->runQueryEquals('simulasi', ['id' => $id], 1);
        return $rows[0]['data'] ?? null;
    }

    public function findLatestActiveByMataPelajaranId(int $mataPelajaranId): ?array
    {
        // Avoid composite index by querying a single field.
        $rows = $this->client->runQueryEquals('simulasi', [
            'mata_pelajaran_id' => $mataPelajaranId,
        ], 200);

        $candidates = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            if (($data['is_active'] ?? null) === true) {
                $candidates[] = $data;
            }
        }

        usort($candidates, function (array $a, array $b) {
            return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
        });

        return $candidates[0] ?? null;
    }

    public function upsertById(int $id, array $data): void
    {
        $data['id'] = $id;
        $this->client->upsertByField('simulasi', 'id', $id, $data);
    }

    public function deleteById(int $id): void
    {
        $docId = $this->client->findDocIdByField('simulasi', 'id', $id);
        if ($docId) {
            $this->client->deleteDocument('simulasi', $docId);
        }
    }
}
