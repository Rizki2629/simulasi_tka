<?php

namespace App\Services;

class FirestoreMataPelajaranStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
    }

    public function getById(int $id): ?array
    {
        $rows = $this->client->runQueryEquals('mata_pelajaran', ['id' => $id], 1);
        return $rows[0]['data'] ?? null;
    }

    public function findByNama(string $nama): ?array
    {
        $rows = $this->client->runQueryEquals('mata_pelajaran', ['nama' => $nama], 1);
        return $rows[0]['data'] ?? null;
    }

    public function upsertById(int $id, array $data): void
    {
        $data['id'] = $id;
        $this->client->upsertByField('mata_pelajaran', 'id', $id, $data);
    }
}
