<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $app;

    public function __construct()
    {
        $credentialsEnv = (string) env('FIREBASE_CREDENTIALS');
        $credentialsPath = $this->resolvePath($credentialsEnv);

        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withProjectId(env('FIREBASE_PROJECT_ID'));

        $this->app = $factory->create();
    }

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1 || str_starts_with($path, '\\\\')) {
            return $path;
        }
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    /**
     * Simpan data ke collection Firestore
     */
    public function create(string $collection, array $data, ?string $documentId = null)
    {
        throw new \RuntimeException('FirebaseService Firestore methods dinonaktifkan (butuh ext-grpc). Gunakan FirestoreRestClient.');
    }

    /**
     * Baca dokumen dari Firestore
     */
    public function read(string $collection, string $documentId)
    {
        throw new \RuntimeException('FirebaseService Firestore methods dinonaktifkan (butuh ext-grpc). Gunakan FirestoreRestClient.');
    }

    /**
     * Baca semua dokumen dari collection
     */
    public function readAll(string $collection, ?int $limit = null)
    {
        throw new \RuntimeException('FirebaseService Firestore methods dinonaktifkan (butuh ext-grpc). Gunakan FirestoreRestClient.');
    }

    /**
     * Update dokumen di Firestore
     */
    public function update(string $collection, string $documentId, array $data)
    {
        throw new \RuntimeException('FirebaseService Firestore methods dinonaktifkan (butuh ext-grpc). Gunakan FirestoreRestClient.');
    }

    /**
     * Hapus dokumen dari Firestore
     */
    public function delete(string $collection, string $documentId)
    {
        throw new \RuntimeException('FirebaseService Firestore methods dinonaktifkan (butuh ext-grpc). Gunakan FirestoreRestClient.');
    }

    /**
     * Query dengan kondisi WHERE
     */
    public function query(string $collection, string $field, string $operator, $value)
    {
        throw new \RuntimeException('FirebaseService Firestore methods dinonaktifkan (butuh ext-grpc). Gunakan FirestoreRestClient.');
    }

    /**
     * Akses langsung ke Firestore database
     */
    public function getDatabase()
    {
        return $this->app;
    }
}
