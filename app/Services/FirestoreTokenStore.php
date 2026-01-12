<?php

namespace App\Services;

use Carbon\Carbon;

class FirestoreTokenStore
{
    public function __construct(private readonly FirestoreRestClient $client)
    {
    }

    public function findValidByToken(string $token): ?array
    {
        // Avoid composite indexes by querying on a single field then filtering in PHP.
        $rows = $this->client->runQueryEquals('tokens', [
            'token' => $token,
        ], 25);

        $now = Carbon::now();
        $candidates = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }

            if (($data['is_active'] ?? null) !== true) {
                continue;
            }
            $expiresAt = $data['expires_at'] ?? null;
            if ($expiresAt instanceof Carbon && $expiresAt->greaterThan($now)) {
                $candidates[] = $data;
            }
        }

        usort($candidates, function (array $a, array $b) {
            $ea = $a['expires_at'] ?? null;
            $eb = $b['expires_at'] ?? null;
            $ia = $ea instanceof Carbon ? $ea->getTimestamp() : 0;
            $ib = $eb instanceof Carbon ? $eb->getTimestamp() : 0;
            return $ib <=> $ia;
        });

        return $candidates[0] ?? null;
    }

    public function getCurrentToken(): ?array
    {
        // Avoid orderBy to prevent index requirement.
        $rows = $this->client->runQueryEquals('tokens', [
            'is_active' => true,
        ], 200);

        $now = Carbon::now();
        $candidates = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }

            $expiresAt = $data['expires_at'] ?? null;
            if ($expiresAt instanceof Carbon && $expiresAt->greaterThan($now)) {
                $candidates[] = $data;
            }
        }

        usort($candidates, function (array $a, array $b) {
            $ea = $a['expires_at'] ?? null;
            $eb = $b['expires_at'] ?? null;
            $ia = $ea instanceof Carbon ? $ea->getTimestamp() : 0;
            $ib = $eb instanceof Carbon ? $eb->getTimestamp() : 0;
            return $ib <=> $ia;
        });

        return $candidates[0] ?? null;
    }

    public function upsertById(int $id, array $data): void
    {
        $data['id'] = $id;
        $this->client->upsertByField('tokens', 'id', $id, $data);
    }
}
