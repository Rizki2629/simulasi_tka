<?php

namespace App\Support;

final class FirebaseCredentials
{
    /**
     * Resolve a readable file path for Firebase service account JSON.
     *
     * Supported inputs:
     * - FIREBASE_CREDENTIALS: path to JSON file (absolute or relative to base_path)
     * - FIREBASE_CREDENTIALS_JSON: raw JSON string
     * - FIREBASE_CREDENTIALS_B64: base64-encoded JSON
     * - FIREBASE_CREDENTIALS: can also be inline JSON (starts with '{')
     * - FIREBASE_CREDENTIALS: can also be 'base64:...'
     */
    public static function resolvePath(): string
    {
        $credentials = (string) env('FIREBASE_CREDENTIALS');
        $json = (string) env('FIREBASE_CREDENTIALS_JSON');
        $b64 = (string) env('FIREBASE_CREDENTIALS_B64');

        // Prefer explicit JSON/B64 config vars
        if (trim($b64) !== '') {
            return self::writeDecodedBase64($b64);
        }

        if (trim($json) !== '') {
            return self::writeJson($json);
        }

        $trimmed = trim($credentials);
        if ($trimmed === '') {
            return '';
        }

        if (str_starts_with($trimmed, 'base64:')) {
            return self::writeDecodedBase64(substr($trimmed, 7));
        }

        if (str_starts_with($trimmed, '{')) {
            return self::writeJson($trimmed);
        }

        return self::resolveFilePath($trimmed);
    }

    private static function resolveFilePath(string $path): string
    {
        // Windows absolute path (C:\...) or UNC (\\server\share)
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1 || str_starts_with($path, '\\\\')) {
            return $path;
        }

        // Unix absolute path
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    private static function writeDecodedBase64(string $b64): string
    {
        $decoded = base64_decode($b64, true);
        if ($decoded === false || trim($decoded) === '') {
            throw new \RuntimeException('FIREBASE_CREDENTIALS_B64 tidak valid atau kosong');
        }

        return self::writeJson($decoded);
    }

    private static function writeJson(string $json): string
    {
        $json = trim($json);
        if ($json === '') {
            throw new \RuntimeException('Firebase credentials JSON kosong');
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Firebase credentials bukan JSON valid');
        }

        // Normalize JSON to a stable format
        $normalized = json_encode($decoded, JSON_UNESCAPED_SLASHES);
        if (!is_string($normalized) || $normalized === '') {
            throw new \RuntimeException('Gagal menormalisasi Firebase credentials JSON');
        }

        $dir = storage_path('app/firebase');
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $path = $dir . DIRECTORY_SEPARATOR . 'service-account.json';
        $ok = @file_put_contents($path, $normalized);
        if ($ok === false) {
            throw new \RuntimeException('Gagal menulis Firebase credentials ke: ' . $path);
        }

        // Best-effort permissions (may be ignored on some systems)
        @chmod($path, 0600);

        return $path;
    }
}
