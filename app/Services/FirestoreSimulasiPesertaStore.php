<?php

namespace App\Services;

use Carbon\Carbon;

class FirestoreSimulasiPesertaStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
    }

    public function key(int $userId, int $simulasiId): string
    {
        return $userId . '_' . $simulasiId;
    }

    /**
     * @return array|null Decoded document fields
     */
    public function getByUserSimulasi(int $userId, int $simulasiId): ?array
    {
        $key = $this->key($userId, $simulasiId);
        $docId = $this->client->findDocIdByField('simulasi_peserta', 'key', $key);
        if ($docId) {
            return $this->client->getDocument('simulasi_peserta', $docId);
        }

        // Fallback for legacy docs (synced from SQL without 'key')
        $rows = $this->client->runQueryEquals('simulasi_peserta', [
            'user_id' => $userId,
            'simulasi_id' => $simulasiId,
        ], 1);

        if (!empty($rows) && isset($rows[0]['docId'])) {
            return $rows[0]['data'] ?? null;
        }

        return null;
    }

    public function upsert(int $userId, int $simulasiId, array $data): void
    {
        $key = $this->key($userId, $simulasiId);

        $payload = array_merge($data, [
            'key' => $key,
            'user_id' => $userId,
            'simulasi_id' => $simulasiId,
            'updated_at' => Carbon::now(),
        ]);

        $docId = $this->client->findDocIdByField('simulasi_peserta', 'key', $key);
        if ($docId) {
            $existing = $this->client->getDocument('simulasi_peserta', $docId);
            if (is_array($existing) && array_key_exists('created_at', $existing)) {
                $payload['created_at'] = $existing['created_at'];
            } else {
                $payload['created_at'] = Carbon::now();
            }
            $this->client->setDocument('simulasi_peserta', $docId, $payload);
            return;
        }

        $rows = $this->client->runQueryEquals('simulasi_peserta', [
            'user_id' => $userId,
            'simulasi_id' => $simulasiId,
        ], 1);

        if (!empty($rows) && isset($rows[0]['docId'])) {
            $existing = $rows[0]['data'] ?? null;
            if (is_array($existing) && array_key_exists('created_at', $existing)) {
                $payload['created_at'] = $existing['created_at'];
            } else {
                $payload['created_at'] = Carbon::now();
            }
            $this->client->setDocument('simulasi_peserta', (string) $rows[0]['docId'], $payload);
            return;
        }

        $payload['created_at'] = Carbon::now();
        $this->client->addDocument('simulasi_peserta', $payload);
    }

    public function delete(int $userId, int $simulasiId): void
    {
        $key = $this->key($userId, $simulasiId);
        $docId = $this->client->findDocIdByField('simulasi_peserta', 'key', $key);
        if (!$docId) {
            $rows = $this->client->runQueryEquals('simulasi_peserta', [
                'user_id' => $userId,
                'simulasi_id' => $simulasiId,
            ], 1);
            $docId = isset($rows[0]['docId']) ? (string) $rows[0]['docId'] : null;
        }

        if ($docId) {
            $this->client->deleteDocument('simulasi_peserta', $docId);
        }
    }

    /**
     * @return array<int, array>
     */
    public function listBySimulasiId(int $simulasiId, int $limit = 2000): array
    {
        $rows = $this->client->runQueryEquals('simulasi_peserta', [
            'simulasi_id' => $simulasiId,
        ], $limit);

        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            $out[] = $data;
        }
        return $out;
    }
}
