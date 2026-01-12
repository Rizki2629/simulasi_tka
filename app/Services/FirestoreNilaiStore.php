<?php

namespace App\Services;

use Carbon\Carbon;

class FirestoreNilaiStore
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
        $docId = $this->client->findDocIdByField('nilai', 'key', $key);
        if ($docId) {
            return $this->client->getDocument('nilai', $docId);
        }

        $rows = $this->client->runQueryEquals('nilai', [
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

        $docId = $this->client->findDocIdByField('nilai', 'key', $key);
        if ($docId) {
            // Preserve created_at if present
            $existing = $this->client->getDocument('nilai', $docId);
            if (is_array($existing) && array_key_exists('created_at', $existing)) {
                $payload['created_at'] = $existing['created_at'];
            } else {
                $payload['created_at'] = Carbon::now();
            }
            $this->client->setDocument('nilai', $docId, $payload);
            return;
        }

        $rows = $this->client->runQueryEquals('nilai', [
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
            $this->client->setDocument('nilai', (string) $rows[0]['docId'], $payload);
            return;
        }

        $payload['created_at'] = Carbon::now();
        $this->client->addDocument('nilai', $payload);
    }

    public function delete(int $userId, int $simulasiId): void
    {
        $key = $this->key($userId, $simulasiId);
        $docId = $this->client->findDocIdByField('nilai', 'key', $key);
        if (!$docId) {
            $rows = $this->client->runQueryEquals('nilai', [
                'user_id' => $userId,
                'simulasi_id' => $simulasiId,
            ], 1);
            $docId = isset($rows[0]['docId']) ? (string) $rows[0]['docId'] : null;
        }

        if ($docId) {
            $this->client->deleteDocument('nilai', $docId);
        }
    }

    /**
     * @return array<int, array>
     */
    public function listByUserId(int $userId, int $limit = 200): array
    {
        // Avoid requiring composite indexes (where + orderBy) by sorting in PHP.
        $rows = $this->client->runQueryEquals('nilai', [
            'user_id' => $userId,
        ], $limit);

        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            $out[] = $data;
        }

        usort($out, function (array $a, array $b) {
            $ta = $a['created_at'] ?? null;
            $tb = $b['created_at'] ?? null;
            $ia = $ta instanceof Carbon ? $ta->getTimestamp() : (is_string($ta) ? strtotime($ta) : 0);
            $ib = $tb instanceof Carbon ? $tb->getTimestamp() : (is_string($tb) ? strtotime($tb) : 0);
            return $ib <=> $ia;
        });

        return $out;
    }

    /**
     * @return array<int, array>
     */
    public function listBySimulasiId(int $simulasiId, int $limit = 2000): array
    {
        // Avoid requiring composite indexes (where + orderBy) by sorting in PHP.
        $rows = $this->client->runQueryEquals('nilai', [
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

        usort($out, function (array $a, array $b) {
            $ta = $a['created_at'] ?? null;
            $tb = $b['created_at'] ?? null;
            $ia = $ta instanceof Carbon ? $ta->getTimestamp() : (is_string($ta) ? strtotime($ta) : 0);
            $ib = $tb instanceof Carbon ? $tb->getTimestamp() : (is_string($tb) ? strtotime($tb) : 0);
            return $ib <=> $ia;
        });

        return $out;
    }

    public function getBySqliteId(int $id): ?array
    {
        $docId = $this->client->findDocIdByField('nilai', 'id', $id);
        if ($docId) {
            return $this->client->getDocument('nilai', $docId);
        }

        $rows = $this->client->runQueryEquals('nilai', ['id' => $id], 1);
        return $rows[0]['data'] ?? null;
    }
}
