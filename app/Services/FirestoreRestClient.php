<?php

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use App\Support\FirebaseCredentials;

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

    /**
     * Create a new document with Firestore auto-generated document ID.
     *
     * @return string Document ID
     */
    public function addDocument(string $collection, array $data): string
    {
        $url = $this->documentsBaseUrl() . '/' . rawurlencode($collection);

        $body = [
            'fields' => $this->encodeFields($data),
        ];

        $json = $this->requestJson('POST', $url, [
            'json' => $body,
        ]);

        $name = (string) ($json['name'] ?? '');
        return $this->extractDocId($name);
    }

    public function deleteDocument(string $collection, string $documentId): void
    {
        $url = $this->documentsBaseUrl() . '/' . rawurlencode($collection) . '/' . rawurlencode($documentId);
        $this->requestJson('DELETE', $url);
    }

    /**
     * Run a StructuredQuery (Firestore runQuery API) against a collection.
     *
     * Supports simple equality filters.
     *
     * @param array<string, mixed> $whereEq
     * @return array<int, array{docId:string,data:array}>
     */
    public function runQueryEquals(string $collection, array $whereEq, int $limit = 100, ?string $orderByField = null, string $orderByDirection = 'DESC'): array
    {
        $url = $this->documentsBaseUrl() . ':runQuery';

        $filters = [];
        foreach ($whereEq as $field => $value) {
            if (!is_string($field) || $field === '') {
                continue;
            }
            $filters[] = [
                'fieldFilter' => [
                    'field' => ['fieldPath' => $field],
                    'op' => 'EQUAL',
                    'value' => $this->encodeValue($value),
                ],
            ];
        }

        $where = null;
        if (count($filters) === 1) {
            $where = $filters[0];
        } elseif (count($filters) > 1) {
            $where = [
                'compositeFilter' => [
                    'op' => 'AND',
                    'filters' => $filters,
                ],
            ];
        }

        $structured = [
            'from' => [
                ['collectionId' => $collection],
            ],
            'limit' => $limit,
        ];

        if ($orderByField !== null && $orderByField !== '') {
            $dir = strtoupper($orderByDirection) === 'ASC' ? 'ASCENDING' : 'DESCENDING';
            $structured['orderBy'] = [
                [
                    'field' => ['fieldPath' => $orderByField],
                    'direction' => $dir,
                ],
            ];
        }
        if ($where !== null) {
            $structured['where'] = $where;
        }

        $json = $this->requestJson('POST', $url, [
            'json' => [
                'structuredQuery' => $structured,
            ],
        ]);

        // runQuery returns an array of results
        if (!is_array($json)) {
            return [];
        }

        $out = [];
        foreach ($json as $row) {
            if (!is_array($row)) {
                continue;
            }
            $doc = $row['document'] ?? null;
            if (!is_array($doc)) {
                continue;
            }
            $name = (string) ($doc['name'] ?? '');
            $docId = $this->extractDocId($name);
            if ($docId === '') {
                continue;
            }
            $data = $this->decodeFields($doc['fields'] ?? []);
            $out[] = [
                'docId' => $docId,
                'data' => $data,
            ];
        }

        return $out;
    }

    public function findDocIdByField(string $collection, string $field, mixed $value): ?string
    {
        $rows = $this->runQueryEquals($collection, [$field => $value], 1);
        if ($rows === [] || !isset($rows[0]['docId'])) {
            return null;
        }
        return (string) $rows[0]['docId'];
    }

    /**
     * Upsert by field equality (commonly: field `id`).
     *
     * If a document is found, updates it; otherwise creates a new document with auto-id.
     *
     * @return string Document ID
     */
    public function upsertByField(string $collection, string $field, mixed $value, array $data): string
    {
        $docId = $this->findDocIdByField($collection, $field, $value);
        if ($docId) {
            $this->setDocument($collection, $docId, $data);
            return $docId;
        }

        return $this->addDocument($collection, $data);
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

        $credentialsPath = FirebaseCredentials::resolvePath();
        if (!is_file($credentialsPath)) {
            throw new \RuntimeException('FIREBASE_CREDENTIALS tidak ditemukan: ' . $credentialsPath);
        }

        $token = $this->fetchAccessTokenWithServiceAccount($credentialsPath);
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
     * Minimal OAuth2 service-account JWT flow.
     * Avoids requiring google/auth (and ext-sodium) on local XAMPP.
     *
     * @return array{access_token?:string,expires_in?:int,token_type?:string}
     */
    private function fetchAccessTokenWithServiceAccount(string $credentialsPath): array
    {
        $raw = file_get_contents($credentialsPath);
        if ($raw === false || trim($raw) === '') {
            throw new \RuntimeException('FIREBASE_CREDENTIALS kosong/tidak bisa dibaca: ' . $credentialsPath);
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            throw new \RuntimeException('FIREBASE_CREDENTIALS bukan JSON valid: ' . $credentialsPath);
        }

        $clientEmail = (string) ($json['client_email'] ?? '');
        $privateKey = (string) ($json['private_key'] ?? '');
        $tokenUri = (string) ($json['token_uri'] ?? 'https://oauth2.googleapis.com/token');

        if ($clientEmail === '' || $privateKey === '') {
            throw new \RuntimeException('FIREBASE_CREDENTIALS tidak memiliki client_email/private_key');
        }

        if (!function_exists('openssl_sign')) {
            throw new \RuntimeException('PHP extension openssl tidak aktif (dibutuhkan untuk sign JWT)');
        }

        $now = time();
        $scopes = 'https://www.googleapis.com/auth/datastore';

        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => $scopes,
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_UNESCAPED_SLASHES));

        $toSign = $header . '.' . $payload;

        $signature = '';
        $ok = openssl_sign($toSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if ($ok !== true) {
            throw new \RuntimeException('Gagal sign JWT untuk service account (cek private_key)');
        }

        $assertion = $toSign . '.' . $this->base64UrlEncode($signature);

        $resp = $this->http->request('POST', $tokenUri, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ],
        ]);

        $body = (string) $resp->getBody();
        $token = json_decode($body, true);
        if (!is_array($token)) {
            throw new \RuntimeException('Response token bukan JSON valid: ' . $body);
        }

        return $token;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
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
            try {
                return Carbon::parse((string) $typed['timestampValue']);
            } catch (\Throwable $e) {
                return (string) $typed['timestampValue'];
            }
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
