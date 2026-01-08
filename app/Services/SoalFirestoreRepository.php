<?php

namespace App\Services;

use Illuminate\Support\Arr;

class SoalFirestoreRepository
{
    public function __construct(
        private readonly FirestoreRestClient $client,
    ) {
    }

    /**
     * @return array<int, array>
     */
    public function listAll(): array
    {
        $rows = $this->client->listCollection('soal');
        usort($rows, function ($a, $b) {
            return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
        });
        return $rows;
    }

    public function find(int $id): ?array
    {
        return $this->client->getDocument('soal', (string) $id);
    }

    /**
     * Create a paket soal document.
     *
     * Expected keys: kode_soal, mata_pelajaran_id, mata_pelajaran_nama, jenis_soal, pertanyaan, bobot, created_by, sub_soal.
     */
    public function create(array $payload): int
    {
        throw new \RuntimeException('Create langsung ke Firestore belum diaktifkan. Gunakan sync dari DB dulu.');
    }

    public function upsertWithId(int $id, array $payload): void
    {
        $data = $this->normalizeSoalPayload($payload);
        $data['id'] = $id;
        $data['updated_at'] = $data['updated_at'] ?? now();
        $data['created_at'] = $data['created_at'] ?? now();

        $this->client->setDocument('soal', (string) $id, $data);
    }

    public function replace(int $id, array $payload): void
    {
        $data = $this->normalizeSoalPayload($payload);
        $data['id'] = $id;
        $data['updated_at'] = $data['updated_at'] ?? now();
        $data['created_at'] = $data['created_at'] ?? now();

        $this->client->setDocument('soal', (string) $id, $data);
    }

    public function delete(int $id): void
    {
        $this->client->deleteDocument('soal', (string) $id);
    }

    private function normalizeSoalPayload(array $payload): array
    {
        $subSoal = Arr::get($payload, 'sub_soal', []);
        if (!is_array($subSoal)) {
            $subSoal = [];
        }

        return [
            'kode_soal' => (string) Arr::get($payload, 'kode_soal', ''),
            'mata_pelajaran_id' => Arr::get($payload, 'mata_pelajaran_id'),
            'mata_pelajaran_nama' => Arr::get($payload, 'mata_pelajaran_nama'),
            'jenis_soal' => (string) Arr::get($payload, 'jenis_soal', 'paket'),
            'pertanyaan' => (string) Arr::get($payload, 'pertanyaan', ''),
            'pembahasan' => Arr::get($payload, 'pembahasan'),
            'gambar_pertanyaan' => Arr::get($payload, 'gambar_pertanyaan'),
            'bobot' => (int) Arr::get($payload, 'bobot', 0),
            'jawaban_benar' => Arr::get($payload, 'jawaban_benar'),
            'created_by' => Arr::get($payload, 'created_by'),
            'created_by_name' => Arr::get($payload, 'created_by_name'),
            'simulasi_soal_count' => (int) Arr::get($payload, 'simulasi_soal_count', 0),
            'created_at' => Arr::get($payload, 'created_at'),
            'updated_at' => Arr::get($payload, 'updated_at'),
            'sub_soal' => $subSoal,
        ];
    }
}
