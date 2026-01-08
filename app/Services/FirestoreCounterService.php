<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreCounterService
{
    public function __construct(
        private readonly FirebaseService $firebase,
    ) {
    }

    public function nextInt(string $counterName): int
    {
        /** @var FirestoreClient $db */
        $db = $this->firebase->getDatabase();

        $counterRef = $db->collection('counters')->document($counterName);

        return $db->runTransaction(function ($transaction) use ($counterRef) {
            $snapshot = $transaction->snapshot($counterRef);

            $current = 0;
            if ($snapshot->exists()) {
                $data = $snapshot->data();
                $current = (int) ($data['current'] ?? 0);
            }

            $next = $current + 1;
            $transaction->set($counterRef, [
                'current' => $next,
                'updated_at' => now(),
            ], ['merge' => true]);

            return $next;
        });
    }
}
