<?php

namespace App\Services;

use Carbon\Carbon;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class FirestoreRestClient
{
    private Client $http;

    /** @var array{access_token:string,expires_at:int}|null */
    private static ?array $tokenCache = null;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => 30,
        ]);
    }

    /**
     * @return array<int, array>
     */
    public function listCollection(string $collection): array
    {
        $url = $this->documentsBaseUrl() . '/' . rawurlencode($collection);
        $json = $this->requestJson('GET', $url);

        $documents = $json['documents'] ?? [];
        if (!is_array($documents)) {
            return [];
        }

        $rows = [];
        foreach ($documents as $doc) {
            if (!is_array($doc)) {
                continue;
            }

            $name = (string) ($doc['name'] ?? '');
            $docId = $this->extractDocId($name);
            $data = $this->decodeFields($doc['fields'] ?? []);
            $data['id'] = (int) ($data['id'] ?? $docId);
            $rows[] = $data;
        }

        return $rows;
    }

    public function getDocument(string $collection, string $documentId): ?array
    {
        $url = $this->documentsBaseUrl() . '/' . rawurlencode($collection) . '/' . rawurlencode($documentId);

        try {
            $json = $this->requestJson('GET', $url);
        } catch (\Throwable $e) {
            // Firestore returns 404 with JSON body; treat as not found
            $msg = $e->getMessage();
            if (str_contains($msg, '404')) {
                return null;
            }
            throw $e;
        }

        $data = $this->decodeFields($json['fields'] ?? []);
        $data['id'] = (int) ($data['id'] ?? (int) $documentId);

        return $data;
    }

    public function setDocument(string $collection, string $documentId, array $data): void
    {
        $url = $this->documentsBaseUrl() . '/' . rawurlencode($collection) . '/' . rawurlencode($documentId);

        $body = [
            'fields' => $this->encodeFields($data),
        ];

        $this->requestJson('PATCH', $url, [
            'json' => $body,
        ]);
    }

    public function deleteDocument(string $collection, string $documentId): void
    {
        $url = $this->documentsBaseUrl() . '/' . rawurlencode($collection) . '/' . rawurlencode($documentId);
        $this->requestJson('DELETE', $url);
    }

    private function documentsBaseUrl(): string
    {
        $projectId = env('FIREBASE_PROJECT_ID');
        if (!$projectId) {
            throw new \RuntimeException('FIREBASE_PROJECT_ID belum di-set');
        }

        $databaseId = env('FIRESTORE_DATABASE_ID', '(default)');
        if (!$databaseId) {
            $databaseId = '(default)';
        }

        return 'https://firestore.googleapis.com/v1/projects/' . rawurlencode($projectId) . '/databases/' . rawurlencode($databaseId) . '/documents';
    }

    private function requestJson(string $method, string $url, array $options = []): array
    {
        $token = $this->getAccessToken();

        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ]);

        $response = $this->http->request($method, $url, $options);
        $body = (string) $response->getBody();

        $json = json_decode($body, true);
        if (!is_array($json)) {
            return [];
        }

        return $json;
    }

    private function getAccessToken(): string
    {
        $cached = self::$tokenCache;
        if ($cached && ($cached['expires_at'] ?? 0) > time() + 60) {
            return $cached['access_token'];
        }

        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
        if (!is_file($credentialsPath)) {
            throw new \RuntimeException('FIREBASE_CREDENTIALS tidak ditemukan: ' . $credentialsPath);
        }

        $scopes = ['https://www.googleapis.com/auth/datastore'];
        $creds = new ServiceAccountCredentials($scopes, $credentialsPath);
        $token = $creds->fetchAuthToken();

        $accessToken = (string) Arr::get($token, 'access_token', '');
        if ($accessToken === '') {
            throw new \RuntimeException('Gagal mengambil access token untuk Firestore');
        }

        $expiresIn = (int) Arr::get($token, 'expires_in', 3600);
        self::$tokenCache = [
            'access_token' => $accessToken,
            'expires_at' => time() + max(60, $expiresIn),
        ];

        return $accessToken;
    }

    /**
     * Encode PHP array into Firestore "fields" structure.
     */
    private function encodeFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }
            $fields[$key] = $this->encodeValue($value);
        }
        return $fields;
    }

    private function encodeValue(mixed $value): array
    {
        if ($value === null) {
            return ['nullValue' => null];
        }

        if ($value instanceof Carbon) {
            return ['timestampValue' => $value->toRfc3339String()];
        }

        if ($value instanceof \DateTimeInterface) {
            return ['timestampValue' => Carbon::instance($value)->toRfc3339String()];
        }

        if (is_bool($value)) {
            return ['booleanValue' => $value];
        }

        if (is_int($value)) {
            return ['integerValue' => (string) $value];
        }

        if (is_float($value)) {
            return ['doubleValue' => $value];
        }

        if (is_string($value)) {
            return ['stringValue' => $value];
        }

        if (is_array($value)) {
            $isAssoc = Arr::isAssoc($value);
            if ($isAssoc) {
                return [
                    'mapValue' => [
                        'fields' => $this->encodeFields($value),
                    ],
                ];
            }

            return [
                'arrayValue' => [
                    'values' => array_map(function ($v) {
                        return $this->encodeValue($v);
                    }, $value),
                ],
            ];
        }

        // Fallback: stringify
        return ['stringValue' => (string) $value];
    }

    /**
     * Decode Firestore "fields" into PHP array.
     */
    private function decodeFields(mixed $fields): array
    {
        if (!is_array($fields)) {
            return [];
        }

        $out = [];
        foreach ($fields as $key => $typedValue) {
            if (!is_string($key)) {
                continue;
            }
            $out[$key] = $this->decodeValue($typedValue);
        }
        return $out;
    }

    private function decodeValue(mixed $typed): mixed
    {
        if (!is_array($typed)) {
            return null;
        }

        if (array_key_exists('nullValue', $typed)) {
            return null;
        }
        if (array_key_exists('booleanValue', $typed)) {
            return (bool) $typed['booleanValue'];
        }
        if (array_key_exists('integerValue', $typed)) {
            return (int) $typed['integerValue'];
        }
        if (array_key_exists('doubleValue', $typed)) {
            return (float) $typed['doubleValue'];
        }
        if (array_key_exists('stringValue', $typed)) {
            return (string) $typed['stringValue'];
        }
        if (array_key_exists('timestampValue', $typed)) {
            return (string) $typed['timestampValue'];
        }
        if (array_key_exists('mapValue', $typed)) {
            $fields = $typed['mapValue']['fields'] ?? [];
            return $this->decodeFields($fields);
        }
        if (array_key_exists('arrayValue', $typed)) {
            $values = $typed['arrayValue']['values'] ?? [];
            if (!is_array($values)) {
                return [];
            }
            return array_map(function ($v) {
                return $this->decodeValue($v);
            }, $values);
        }

        return null;
    }

    private function extractDocId(string $documentName): string
    {
        // name = projects/{project}/databases/(default)/documents/{collection}/{docId}
        $parts = explode('/', $documentName);
        return (string) end($parts);
    }
}
