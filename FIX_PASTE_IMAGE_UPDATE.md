# Fix Paste Image dan Gambar Pernyataan

## Masalah yang Diperbaiki

### 1. Gambar Paste Tidak Muncul Saat Edit
**Masalah**: Gambar yang diupload menggunakan paste image tidak muncul kembali saat soal dibuka untuk edit.

**Penyebab**: Hidden input untuk menyimpan path gambar paste tidak dibuat dengan benar saat edit mode.

**Solusi**:
- Update fungsi `showExistingImage()` di `edit.blade.php`
- Perbaiki logic pembuatan nama hidden input berdasarkan preview ID
- Format naming yang benar:
  - `preview-soal-1` → `gambar_soal_1`
  - `preview-a-1` → `gambar_pilihan_1_a`
  - `preview-pernyataan-1-2` → `gambar_pernyataan_1_2`
- Menambahkan `pernyataan-item` ke daftar parent container untuk mencari hidden input

### 2. Paste Image Tidak Berfungsi Untuk Pernyataan Benar Salah dan MCMA
**Masalah**: Fitur paste image hanya bekerja untuk pertanyaan dan pilihan jawaban, tidak bekerja untuk pernyataan pada soal Benar Salah dan MCMA.

**Penyebab**: Input pernyataan tidak memiliki upload container dan preview area untuk menampilkan gambar paste.

**Solusi**:
- Menambahkan upload container dan preview area untuk setiap pernyataan
- Update fungsi `tambahPernyataan()` dan `tambahPernyataanMCMA()` untuk include upload container
- Update template HTML pernyataan pertama (dalam `ubahJenisSoal()`)
- Menambahkan handler di controller untuk menyimpan gambar pernyataan
- Update `syncPernyataanJawaban()` untuk handle file upload dan paste image
- Update JavaScript paste handler untuk mencari container di `pernyataan-item`

## File yang Dimodifikasi

### 1. resources/views/soal/edit.blade.php
**Perubahan**:
- ✅ Update `showExistingImage()`: Perbaiki logic pembuatan hidden input
- ✅ Update `tambahPernyataan()`: Tambah upload container untuk Benar Salah
- ✅ Update `tambahPernyataanMCMA()`: Tambah upload container untuk MCMA
- ✅ Update `populateBenarSalahData()`: Tampilkan gambar pernyataan saat edit
- ✅ Update `populateMcmaData()`: Tampilkan gambar pernyataan saat edit

### 2. resources/views/soal/create.blade.php
**Perubahan**:
- ✅ Tambah tip paste image pada label pernyataan
- ✅ Update template pernyataan pertama Benar Salah: Tambah upload container
- ✅ Update template pernyataan pertama MCMA: Tambah upload container

### 3. app/Http/Controllers/SoalController.php
**Perubahan**:
- ✅ Update `syncPernyataanJawaban()`: Handle gambar pernyataan dari file upload atau paste
- ✅ Simpan gambar pernyataan ke kolom `gambar_jawaban` di tabel `pilihan_jawaban`

### 4. public/js/paste-image-upload.js
**Perubahan**:
- ✅ Update `handleSuccess()`: Tambah `pernyataan-item` ke strategy pencarian container
- ✅ Update `showPreview()`: Tambah `pernyataan-item` ke parent container lookup

## Cara Kerja

### Upload Gambar Pernyataan
1. **Manual Upload**: Klik tombol "Upload Gambar Pernyataan"
2. **Paste Image**: 
   - Focus pada input pernyataan
   - Paste gambar dari clipboard (Ctrl+V)
   - Gambar otomatis terupload dan preview ditampilkan di bawah input

### Penyimpanan Database
- Gambar pernyataan disimpan di kolom `gambar_jawaban` tabel `pilihan_jawaban`
- Path gambar: `storage/pernyataan/{filename}`
- Format nama field:
  - File upload: `gambar_pernyataan_{soalId}_{pernyataanNumber}`
  - Hidden input (paste): `gambar_pernyataan_{soalId}_{pernyataanNumber}`

### Edit Mode
1. Saat membuka soal untuk edit, fungsi `populateBenarSalahData()` atau `populateMcmaData()` dipanggil
2. Untuk setiap pernyataan dengan gambar, panggil `showExistingImage()`
3. Hidden input dibuat dengan format: `gambar_pernyataan_{soalId}_{pernyataanNumber}`
4. Gambar ditampilkan di preview area
5. Saat submit, hidden input dikirim ke server bersama form data

## Naming Convention

### Preview ID Format
- Soal: `preview-soal-{soalId}`
- Pilihan jawaban: `preview-{a|b|c|d|e}-{soalId}`
- Pernyataan: `preview-pernyataan-{soalId}-{pernyataanNumber}`

### Hidden Input Name Format
- Gambar soal: `gambar_soal_{soalId}`
- Gambar pilihan: `gambar_pilihan_{soalId}_{a|b|c|d|e}`
- Gambar pernyataan: `gambar_pernyataan_{soalId}_{pernyataanNumber}`

## Testing Checklist

### Test Case 1: Paste Image Pernyataan (Create Mode)
- [ ] Buat soal Benar Salah baru
- [ ] Focus pada input pernyataan 1
- [ ] Paste gambar (Ctrl+V)
- [ ] Verifikasi preview muncul
- [ ] Tambah pernyataan 2, paste gambar lagi
- [ ] Submit form
- [ ] Verifikasi gambar tersimpan di database

### Test Case 2: Paste Image Pernyataan (Edit Mode)
- [ ] Buka soal Benar Salah yang sudah ada gambar pernyataan
- [ ] Verifikasi gambar pernyataan tampil
- [ ] Edit tanpa mengubah gambar, submit
- [ ] Verifikasi gambar tetap ada
- [ ] Edit dan paste gambar baru pada pernyataan
- [ ] Submit, verifikasi gambar baru tersimpan

### Test Case 3: MCMA dengan Gambar Pernyataan
- [ ] Buat soal MCMA baru
- [ ] Paste gambar pada pernyataan 1, 2, 3
- [ ] Verifikasi preview semua gambar
- [ ] Submit dan buka untuk edit
- [ ] Verifikasi semua gambar tampil
- [ ] Submit lagi tanpa perubahan
- [ ] Verifikasi gambar tetap ada

### Test Case 4: Mix Upload dan Paste
- [ ] Buat soal dengan 3 pernyataan
- [ ] Pernyataan 1: Upload manual
- [ ] Pernyataan 2: Paste image
- [ ] Pernyataan 3: Tanpa gambar
- [ ] Submit dan edit
- [ ] Verifikasi pernyataan 1 dan 2 memiliki gambar
- [ ] Pernyataan 3: Paste image baru
- [ ] Submit, verifikasi semua tersimpan

## Console Debugging
Fungsi `showExistingImage()` sekarang menampilkan log:
```
Created/Updated hidden input: gambar_pernyataan_1_2 = pernyataan/xyz.jpg
```

Gunakan browser console untuk memverifikasi hidden input dibuat dengan benar.

## Tips Penggunaan
1. 💡 **Paste Langsung**: Focus pada input pernyataan, lalu Ctrl+V
2. 🖼️ **Preview Otomatis**: Gambar langsung tampil setelah paste berhasil
3. ❌ **Hapus Gambar**: Klik tombol "Hapus" pada preview
4. ✏️ **Edit Mode**: Gambar lama otomatis tampil saat buka edit
5. 🔄 **Update Gambar**: Hapus gambar lama, paste/upload yang baru

## Tanggal Update
30 November 2025
