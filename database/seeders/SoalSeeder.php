<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Soal;
use App\Models\User;
use App\Models\MataPelajaran;
use App\Models\PilihanJawaban;

class SoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mataPelajaran = MataPelajaran::where('kode', 'MTK')->first();
        $admin = User::where('role', 'admin')->first();

        if (!$mataPelajaran || !$admin) {
            $this->command?->warn('Mata pelajaran Matematika atau admin tidak ditemukan. SoalSeeder dilewati.');
            return;
        }

        $images = $this->prepareImages();

        $this->seedPilihanGanda($mataPelajaran->id, $admin->id, $images);
        $this->seedMcma($mataPelajaran->id, $admin->id, $images);
        $this->seedUraian($mataPelajaran->id, $admin->id, $images);
    }

    private function prepareImages(): array
    {
        Storage::disk('public')->makeDirectory('dummy');

        return [
            'square' => $this->storeSvg('dummy/persegi.svg', '#1e3a8a', '8 cm'),
            'triangle' => $this->storeSvg('dummy/segitiga.svg', '#0ea5e9', 'Delta'),
            'grid' => $this->storeSvg('dummy/prisma.svg', '#f97316', 'Net'),
            'solution' => $this->storeSvg('dummy/pembahasan.svg', '#16a34a', 'Solusi'),
            'optionA' => $this->storeSvg('dummy/opsi-a.svg', '#2563eb', 'A'),
            'optionB' => $this->storeSvg('dummy/opsi-b.svg', '#7c3aed', 'B'),
            'optionC' => $this->storeSvg('dummy/opsi-c.svg', '#f59e0b', 'C'),
            'optionD' => $this->storeSvg('dummy/opsi-d.svg', '#dc2626', 'D'),
        ];
    }

    private function storeSvg(string $relativePath, string $color, string $label): string
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($relativePath)) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200">
    <rect width="100%" height="100%" rx="24" fill="{$color}" />
    <text x="50%" y="55%" font-family="'Arial', sans-serif" font-size="36" fill="#ffffff" dominant-baseline="middle" text-anchor="middle">{$safeLabel}</text>
</svg>
SVG;
            $disk->put($relativePath, $svg);
        }

        return $relativePath;
    }

    private function seedPilihanGanda(int $mataPelajaranId, int $adminId, array $images): void
    {
        $soal = Soal::updateOrCreate(
            ['kode_soal' => 'MTK-DUMMY-PG'],
            [
                'mata_pelajaran_id' => $mataPelajaranId,
                'jenis_soal' => 'pilihan_ganda',
                'pertanyaan' => 'Perhatikan persegi pada gambar. Jika panjang setiap sisinya 8 cm, berapakah luas persegi tersebut?',
                'gambar_pertanyaan' => $images['square'],
                'pembahasan' => 'Luas persegi = sisi x sisi = 8 x 8 = 64 cm^2.',
                'gambar_pembahasan' => $images['solution'],
                'kunci_jawaban' => null,
                'jawaban_benar' => null,
                'bobot' => 1,
                'created_by' => $adminId,
            ]
        );

        $options = [
            ['label' => 'A', 'text' => '64 cm^2', 'image' => $images['optionA']],
            ['label' => 'B', 'text' => '48 cm^2', 'image' => $images['optionB']],
            ['label' => 'C', 'text' => '32 cm^2', 'image' => $images['optionC']],
            ['label' => 'D', 'text' => '16 cm^2', 'image' => $images['optionD']],
        ];

        $this->syncOptions($soal, $options, ['A']);
    }

    private function seedMcma(int $mataPelajaranId, int $adminId, array $images): void
    {
        $soal = Soal::updateOrCreate(
            ['kode_soal' => 'MTK-DUMMY-MCMA'],
            [
                'mata_pelajaran_id' => $mataPelajaranId,
                'jenis_soal' => 'mcma',
                'pertanyaan' => 'Perhatikan segitiga siku-siku pada gambar. Pilih semua pernyataan yang benar tentang segitiga tersebut.',
                'gambar_pertanyaan' => $images['triangle'],
                'pembahasan' => 'Segitiga memiliki sudut siku-siku dan luas 24 cm^2 (1/2 x alas 8 cm x tinggi 6 cm). Kelilingnya 8 + 6 + 10 = 24 cm.',
                'gambar_pembahasan' => $images['solution'],
                'kunci_jawaban' => null,
                'jawaban_benar' => null,
                'bobot' => 1,
                'created_by' => $adminId,
            ]
        );

        $options = [
            ['label' => 'A', 'text' => 'Segitiga memiliki sudut siku-siku.', 'image' => $images['optionA']],
            ['label' => 'B', 'text' => 'Keliling segitiga adalah 18 cm.', 'image' => $images['optionB']],
            ['label' => 'C', 'text' => 'Luas segitiga adalah 24 cm^2.', 'image' => $images['optionC']],
            ['label' => 'D', 'text' => 'Tinggi segitiga adalah 4 cm.', 'image' => $images['optionD']],
        ];

        $this->syncOptions($soal, $options, ['A', 'C']);
    }

    private function seedUraian(int $mataPelajaranId, int $adminId, array $images): void
    {
        $jawaban = "Volume prisma = luas alas x tinggi = (10 cm x 6 cm) x 10 cm = 600 cm^3.";

        Soal::updateOrCreate(
            ['kode_soal' => 'MTK-DUMMY-URAIAN'],
            [
                'mata_pelajaran_id' => $mataPelajaranId,
                'jenis_soal' => 'uraian',
                'pertanyaan' => 'Perhatikan jaring-jaring prisma pada gambar. Jika alas prisma berbentuk persegi panjang berukuran 10 cm x 6 cm dan tinggi prisma 10 cm, hitung volume prisma tersebut.',
                'gambar_pertanyaan' => $images['grid'],
                'pembahasan' => $jawaban,
                'gambar_pembahasan' => $images['solution'],
                'kunci_jawaban' => $jawaban,
                'jawaban_benar' => $jawaban,
                'bobot' => 2,
                'created_by' => $adminId,
            ]
        )->pilihanJawaban()->delete();
    }

    private function syncOptions(Soal $soal, array $options, array $correctLabels): void
    {
        $soal->pilihanJawaban()->delete();

        foreach ($options as $option) {
            PilihanJawaban::create([
                'soal_id' => $soal->id,
                'label' => strtoupper($option['label']),
                'teks_jawaban' => $option['text'],
                'gambar_jawaban' => $option['image'] ?? null,
                'is_benar' => in_array(strtoupper($option['label']), $correctLabels, true),
            ]);
        }

        $soal->jawaban_benar = implode(',', array_map('strtoupper', $correctLabels));
        $soal->save();
    }
}
