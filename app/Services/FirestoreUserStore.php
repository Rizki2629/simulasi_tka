<?php

namespace App\Services;

class FirestoreUserStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
    }

    public function findStudentByNisn(string $nisn): ?array
    {
        // Avoid composite index by querying by nisn only.
        $rows = $this->client->runQueryEquals('users', [
            'nisn' => $nisn,
        ], 10);

        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            if (($data['role'] ?? null) === 'siswa') {
                return $data;
            }
        }

        return null;
    }

    public function findById(int $id): ?array
    {
        $rows = $this->client->runQueryEquals('users', ['id' => $id], 1);
        return $rows[0]['data'] ?? null;
    }

    public function upsertById(int $id, array $data): void
    {
        $data['id'] = $id;
        $this->client->upsertByField('users', 'id', $id, $data);
    }
}
