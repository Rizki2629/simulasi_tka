<?php

namespace App\Services;

use Carbon\Carbon;

class FirestoreJawabanPesertaStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
    }

    public function key(int $userId, int $simulasiId, int $soalId): string
    {
        return $userId . '_' . $simulasiId . '_' . $soalId;
    }

    public function upsert(int $userId, int $simulasiId, int $soalId, array $data): void
    {
        $key = $this->key($userId, $simulasiId, $soalId);

        $payload = array_merge($data, [
            'key' => $key,
            'user_id' => $userId,
            'simulasi_id' => $simulasiId,
            'soal_id' => $soalId,
            'updated_at' => Carbon::now(),
        ]);

        $docId = $this->client->findDocIdByField('jawaban_peserta', 'key', $key);
        if ($docId) {
            $existing = $this->client->getDocument('jawaban_peserta', $docId);
            if (is_array($existing) && array_key_exists('created_at', $existing)) {
                $payload['created_at'] = $existing['created_at'];
            } else {
                $payload['created_at'] = Carbon::now();
            }
            $this->client->setDocument('jawaban_peserta', $docId, $payload);
            return;
        }

        $rows = $this->client->runQueryEquals('jawaban_peserta', [
            'user_id' => $userId,
            'simulasi_id' => $simulasiId,
            'soal_id' => $soalId,
        ], 1);

        if (!empty($rows) && isset($rows[0]['docId'])) {
            $existing = $rows[0]['data'] ?? null;
            if (is_array($existing) && array_key_exists('created_at', $existing)) {
                $payload['created_at'] = $existing['created_at'];
            } else {
                $payload['created_at'] = Carbon::now();
            }
            $this->client->setDocument('jawaban_peserta', (string) $rows[0]['docId'], $payload);
            return;
        }

        $payload['created_at'] = Carbon::now();
        $this->client->addDocument('jawaban_peserta', $payload);
    }

    public function deleteAllForUserSimulasi(int $userId, int $simulasiId): void
    {
        $rows = $this->client->runQueryEquals('jawaban_peserta', [
            'user_id' => $userId,
            'simulasi_id' => $simulasiId,
        ], 2000);

        foreach ($rows as $row) {
            $docId = $row['docId'] ?? null;
            if (is_string($docId) && $docId !== '') {
                $this->client->deleteDocument('jawaban_peserta', $docId);
            }
        }
    }

    /**
     * @return array<int, array>
     */
    public function listByUserSimulasi(int $userId, int $simulasiId, int $limit = 2000): array
    {
        // Avoid composite indexes by querying a single field.
        $rows = $this->client->runQueryEquals('jawaban_peserta', [
            'user_id' => $userId,
        ], $limit);

        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }
            if ((int) ($data['simulasi_id'] ?? 0) !== $simulasiId) {
                continue;
            }
            $out[] = $data;
        }

        usort($out, fn (array $a, array $b) => ((int) ($a['soal_id'] ?? 0)) <=> ((int) ($b['soal_id'] ?? 0)));
        return $out;
    }
}
