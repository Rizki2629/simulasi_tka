# Sistem Penilaian Simulasi TKA

## Overview
Sistem penilaian otomatis untuk simulasi ujian dengan 3 jenis soal:
- **Pilihan Ganda**: Jawaban benar = 1 poin
- **Benar Salah**: Setiap pernyataan benar = 1 poin (jika 4 pernyataan = maksimal 4 poin)
- **MCMA (Multiple Choice Multiple Answer)**: Setiap pilihan benar = 1 poin

## Database Schema

### Tabel: `nilai`
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK ke tabel users |
| simulasi_id | bigint | FK ke tabel simulasi |
| mata_pelajaran_id | bigint | FK ke tabel mata_pelajaran |
| nilai_total | decimal(8,2) | Total nilai yang didapat |
| jumlah_benar | integer | Jumlah jawaban benar |
| jumlah_salah | integer | Jumlah jawaban salah |
| jumlah_soal | integer | Total soal yang dikerjakan |
| detail_jawaban | text | JSON detail per soal |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Unique constraint**: (`user_id`, `simulasi_id`)

## Cara Kerja Sistem

### 1. Saat Siswa Selesai Mengerjakan Ujian
- Method `finishExam()` di `SimulasiController` akan dipanggil
- Sistem mengambil jawaban siswa dari session
- Memanggil `PenilaianService` untuk menghitung nilai

### 2. Perhitungan Nilai (`PenilaianService`)

#### Pilihan Ganda
```php
// Jawaban benar = 1 poin, salah = 0 poin
if ($jawabanUser == $kunciJawaban) {
    $nilai = 1;
} else {
    $nilai = 0;
}
```

#### Benar Salah
```php
// Setiap pernyataan dinilai terpisah
// Contoh: 4 pernyataan, maksimal 4 poin
foreach ($subSoals as $subSoal) {
    if ($jawabanUser[$subSoal->id] == $subSoal->jawaban_benar) {
        $nilai += 1; // Benar = +1
    }
}
```

#### MCMA (Pilihan Ganda Kompleks)
```php
// Setiap pilihan dinilai terpisah
// Contoh: 4 pilihan, maksimal 4 poin
foreach ($subSoals as $subSoal) {
    if ($jawabanUser[$subSoal->id] == $subSoal->jawaban_benar) {
        $nilai += 1; // Benar = +1
    }
}
```

### 3. Penyimpanan Hasil
- Nilai disimpan ke tabel `nilai`
- Status simulasi peserta diupdate menjadi `selesai`
- Kolom `nilai` di tabel `simulasi_peserta` juga diupdate
- Detail jawaban disimpan dalam format JSON

## Fitur yang Tersedia

### 1. Halaman Hasil Ujian (`/simulasi/hasil`)
Menampilkan:
- Nilai total yang didapat
- Statistik: jumlah benar, salah, dan total soal
- Ringkasan per soal (benar/salah)

### 2. Riwayat Nilai (`/simulasi/riwayat-nilai`)
Menampilkan:
- Daftar semua ujian yang pernah dikerjakan
- Nilai setiap ujian
- Tanggal pengerjaan
- Link ke detail pembahasan

### 3. Detail Nilai & Pembahasan (`/simulasi/nilai/{id}`)
Menampilkan:
- Detail setiap soal
- Jawaban siswa vs jawaban yang benar
- Pembahasan soal (jika ada)
- Gambar soal dan pembahasan (jika ada)

## Routes yang Ditambahkan

```php
// Hasil dan Nilai Routes
Route::get('/simulasi/hasil', [SimulasiController::class, 'hasilUjian'])
    ->name('simulasi.hasil');
    
Route::get('/simulasi/riwayat-nilai', [SimulasiController::class, 'riwayatNilai'])
    ->name('simulasi.riwayat.nilai');
    
Route::get('/simulasi/nilai/{id}', [SimulasiController::class, 'detailNilai'])
    ->name('simulasi.detail.nilai');
```

## Contoh Penggunaan

### Contoh Nilai Siswa A - Ujian Matematika

**Soal yang dikerjakan:**
1. Pilihan Ganda (1 soal) = 1 poin
2. Benar Salah (1 soal dengan 4 pernyataan) = 4 poin maksimal
3. MCMA (2 soal dengan masing-masing 3 pernyataan) = 6 poin maksimal

**Total maksimal:** 1 + 4 + 6 = 11 poin

**Hasil Siswa A:**
- Pilihan Ganda: Benar (1 poin)
- Benar Salah: 3 dari 4 benar (3 poin)
- MCMA Soal 1: 2 dari 3 benar (2 poin)
- MCMA Soal 2: 3 dari 3 benar (3 poin)

**Total nilai:** 1 + 3 + 2 + 3 = **9 poin**

### Data yang Disimpan di Database

```json
{
    "user_id": 1,
    "simulasi_id": 5,
    "mata_pelajaran_id": 1,
    "nilai_total": 9,
    "jumlah_benar": 9,
    "jumlah_salah": 2,
    "jumlah_soal": 4,
    "detail_jawaban": [
        {
            "soal_id": 1,
            "jenis_soal": "pilihan_ganda",
            "jawaban_user": "A",
            "jawaban_benar": "A",
            "nilai": 1,
            "maksimal": 1
        },
        {
            "soal_id": 2,
            "jenis_soal": "benar_salah",
            "nilai": 3,
            "maksimal": 4,
            "detail": [
                {"sub_soal_id": 1, "jawaban_user": "B", "jawaban_benar": "B", "benar": 1},
                {"sub_soal_id": 2, "jawaban_user": "B", "jawaban_benar": "S", "benar": 0},
                {"sub_soal_id": 3, "jawaban_user": "B", "jawaban_benar": "B", "benar": 1},
                {"sub_soal_id": 4, "jawaban_user": "S", "jawaban_benar": "S", "benar": 1}
            ]
        }
    ]
}
```

## File yang Dibuat/Dimodifikasi

### Baru
1. `database/migrations/2025_11_30_071702_create_nilai_table.php`
2. `app/Models/Nilai.php`
3. `app/Services/PenilaianService.php`
4. `resources/views/student/hasil-ujian.blade.php`
5. `resources/views/student/riwayat-nilai.blade.php`
6. `resources/views/student/detail-nilai.blade.php`

### Dimodifikasi
1. `app/Http/Controllers/SimulasiController.php`
   - Method `finishExam()`: Implementasi penilaian lengkap
   - Method baru: `hasilUjian()`, `riwayatNilai()`, `detailNilai()`
2. `routes/web.php`: Menambahkan 3 route baru
3. `resources/views/simulasi/student-dashboard.blade.php`: Tambah link riwayat nilai

## Testing

Untuk menguji sistem penilaian:

1. **Login sebagai siswa** dengan token
2. **Konfirmasi data** dan mulai ujian
3. **Kerjakan soal** dengan berbagai jenis
4. **Selesaikan ujian** dengan klik tombol finish
5. **Periksa hasil** di halaman hasil ujian
6. **Lihat pembahasan** untuk review jawaban
7. **Cek riwayat** di menu riwayat nilai

## Catatan Penting

- Nilai otomatis tersimpan saat siswa menyelesaikan ujian
- Siswa dapat melihat jawaban yang benar dan salah setelah selesai
- Sistem mendukung pembahasan dengan teks dan gambar
- Data detail jawaban disimpan dalam format JSON untuk fleksibilitas
- Unique constraint memastikan satu siswa hanya punya satu nilai per simulasi
