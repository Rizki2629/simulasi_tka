# 🖼️ Paste Image Upload - Quick Guide

## ✨ Fitur Baru: Upload Gambar dengan Paste!

Sekarang Anda tidak perlu lagi mengklik tombol upload untuk menambahkan gambar pada soal!

## 🚀 Cara Menggunakan

### Langkah Sederhana:

1. **Screenshot atau Copy Gambar**
   - Tekan `Windows + Shift + S` untuk screenshot
   - Atau klik kanan gambar → Copy image

2. **Klik pada Input/Textarea**
   - Klik pada textarea pertanyaan
   - Atau klik pada **input pilihan jawaban** (A, B, C, D)
   - Atau klik pada textarea pembahasan

3. **Paste!**
   - Tekan `Ctrl + V`
   - 🎉 Gambar otomatis terupload!

### Contoh Workflow:

```
Screenshot (Win+Shift+S) 
    ↓
Klik textarea pertanyaan
    ↓
Ctrl + V
    ↓
✅ Gambar langsung muncul!
```

## 📍 Dimana Fitur Ini Tersedia?

✅ Halaman **Buat Soal Baru** (`/soal/create`)
✅ Halaman **Edit Soal** (`/soal/{id}/edit`)

## 💡 Tips & Tricks

### Tip #1: Screenshot Cepat
```
Windows + Shift + S = Screenshot area tertentu
Windows + PrtScn     = Screenshot full screen
```

### Tip #2: Copy dari Browser
- Klik kanan pada gambar di browser
- Pilih "Copy image"
- Paste di form soal

### Tip #3: Multiple Images
- Bisa paste banyak gambar
- Setiap soal/pilihan bisa punya gambar sendiri
- Tidak ada batasan jumlah

### Tip #4: Menghapus Gambar
- Klik tombol "X" di pojok gambar
- Gambar akan terhapus dari preview
- Bisa paste ulang jika perlu

## 🎯 Format yang Didukung

- ✅ PNG
- ✅ JPEG/JPG
- ✅ GIF
- ✅ BMP
- ✅ WebP

## ⚡ Keuntungan

| Cara Lama | Cara Baru (Paste) |
|-----------|-------------------|
| Screenshot → Save file → Browse → Upload | Screenshot → Paste |
| ~30 detik | ~2 detik |
| 4 langkah | 2 langkah |

**Hemat waktu hingga 90%!** ⚡

## 🔔 Notifikasi

### Saat Upload Berhasil:
```
✅ Gambar berhasil diupload!
```

### Saat Upload Gagal:
```
❌ Terjadi kesalahan saat upload gambar
```

## ❓ FAQ

**Q: Apakah menggantikan tombol upload?**
A: Tidak. Tombol upload masih bisa digunakan. Paste hanya sebagai alternatif yang lebih cepat.

**Q: Apakah ada batasan ukuran file?**
A: Sama dengan batasan PHP upload limit (default 2MB, bisa dinaikkan di php.ini).

**Q: Apakah bisa paste dari clipboard history?**
A: Ya, selama gambar masih ada di clipboard sistem operasi.

**Q: Apakah perlu koneksi internet?**
A: Ya, karena gambar diupload ke server.

**Q: Bagaimana jika paste tidak bekerja?**
A: Pastikan JavaScript enabled dan refresh halaman.

## 🛠️ Troubleshooting

### Paste tidak bekerja?
1. Refresh halaman (F5)
2. Pastikan JavaScript enabled
3. Check browser console (F12)

### Preview tidak muncul?
1. Check internet connection
2. Pastikan storage link ada: `php artisan storage:link`
3. Check permissions folder storage

### Upload sangat lambat?
1. Compress gambar sebelum screenshot
2. Gunakan format PNG untuk gambar simple
3. Check koneksi internet

## 📝 Catatan

- Fitur ini menggunakan **Clipboard API** modern
- Kompatibel dengan browser modern (Chrome, Firefox, Safari, Edge)
- Upload dilakukan secara **asynchronous** (tidak reload halaman)
- Gambar tersimpan di `storage/app/public/soal_images/`

## 🎓 Best Practices

1. **Gunakan screenshot tool Windows** untuk hasil terbaik
2. **Crop gambar** sebelum screenshot untuk ukuran lebih kecil
3. **Paste langsung** setelah screenshot (jangan copy hal lain dulu)
4. **Cek preview** sebelum submit form untuk memastikan gambar benar

---

**Selamat menggunakan fitur baru! Membuat soal sekarang jauh lebih cepat!** 🚀
