<?php

namespace App\Services;

use Carbon\Carbon;

class FirestoreExamSessionStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
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

        // Try by key first
        $docId = $this->client->findDocIdByField('exam_sessions', 'key', $key);
        if ($docId) {
            $this->client->setDocument('exam_sessions', $docId, $payload);
            return;
        }

        // Fallback for legacy docs (synced from SQL without 'key')
        $rows = $this->client->runQueryEquals('exam_sessions', [
            'user_id' => $userId,
            'simulasi_id' => $simulasiId,
        ], 1);

        if (!empty($rows) && isset($rows[0]['docId'])) {
            $this->client->setDocument('exam_sessions', (string) $rows[0]['docId'], $payload);
            return;
        }

        $payload['created_at'] = Carbon::now();
        $this->client->addDocument('exam_sessions', $payload);
    }

    /**
     * @return array<int, array>
     */
    public function listBySimulasiId(int $simulasiId): array
    {
        $rows = $this->client->runQueryEquals('exam_sessions', [
            'simulasi_id' => $simulasiId,
        ], 2000);

        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            $uid = (int) ($data['user_id'] ?? 0);
            if ($uid <= 0) {
                continue;
            }
            $out[$uid] = $data;
        }
        return $out;
    }

    public function delete(int $userId, int $simulasiId): void
    {
        $key = $this->key($userId, $simulasiId);
        $docId = $this->client->findDocIdByField('exam_sessions', 'key', $key);
        if (!$docId) {
            $rows = $this->client->runQueryEquals('exam_sessions', [
                'user_id' => $userId,
                'simulasi_id' => $simulasiId,
            ], 1);
            $docId = isset($rows[0]['docId']) ? (string) $rows[0]['docId'] : null;
        }

        if ($docId) {
            $this->client->deleteDocument('exam_sessions', $docId);
        }
    }

    private function key(int $userId, int $simulasiId): string
    {
        return $userId . '_' . $simulasiId;
    }
}
