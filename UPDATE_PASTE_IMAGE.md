# Update: Paste Image untuk Pilihan Jawaban

## Perubahan

### Masalah
Fitur paste image hanya bekerja untuk pertanyaan soal, tapi **tidak bekerja** untuk pilihan jawaban (A, B, C, D).

### Penyebab
1. Pilihan jawaban menggunakan `input type="text"` bukan `textarea`
2. JavaScript hanya menangkap focus event pada `textarea`
3. Struktur HTML pilihan jawaban berbeda (nested dalam `.pilihan-item`)

### Solusi

#### 1. Update Focus Detection
**Sebelum:**
```javascript
if (e.target.matches('textarea, [contenteditable="true"]')) {
    this.activeElement = e.target;
}
```

**Setelah:**
```javascript
if (e.target.matches('textarea, input[type="text"], [contenteditable="true"]')) {
    this.activeElement = e.target;
}
```

✅ Sekarang menangkap focus pada input text juga

#### 2. Update Container Detection
**Sebelum:**
```javascript
const formGroup = this.activeElement.closest('.form-group, .soal-content, .sub-soal-item');
const previewContainer = formGroup.querySelector('[id^="preview-"]');
```

**Setelah:**
```javascript
// Strategy 1: Try pilihan-item first
let container = this.activeElement.closest('.pilihan-item');
if (container) {
    const uploadContainer = container.querySelector('.upload-container');
    previewContainer = uploadContainer.querySelector('[id^="preview-"]');
}

// Strategy 2: Try form-group
if (!previewContainer) {
    container = this.activeElement.closest('.form-group');
    previewContainer = container.querySelector('[id^="preview-"]');
}

// Strategy 3: Try siblings
if (!previewContainer) {
    // Look in parent's children or siblings
}
```

✅ Multi-strategy untuk menemukan preview container yang tepat

#### 3. Update Parent Container Search
**Di method `showPreview` dan `removePreview`:**

**Sebelum:**
```javascript
const formGroup = container.closest('.form-group, .soal-content, .sub-soal-item');
```

**Setelah:**
```javascript
const parentContainer = container.closest('.pilihan-item, .form-group, .soal-content, .sub-soal-item, .upload-container');
```

✅ Mencari parent container dengan prioritas `.pilihan-item` dulu

## Struktur HTML

### Pertanyaan Soal
```html
<div class="form-group">
    <textarea name="pertanyaan_123"></textarea>
    <div class="upload-container">
        <div id="preview-soal-123"></div>
    </div>
</div>
```

### Pilihan Jawaban
```html
<div class="pilihan-item">
    <input type="text" name="pilihan_123_a">
    <div class="upload-container">
        <div id="preview-a-123"></div>
    </div>
</div>
```

## Testing

### Test Case 1: Paste di Pertanyaan
1. Klik textarea pertanyaan
2. Paste gambar (Ctrl+V)
3. ✅ Gambar muncul di preview-soal-123

### Test Case 2: Paste di Pilihan A
1. Klik input pilihan A
2. Paste gambar (Ctrl+V)
3. ✅ Gambar muncul di preview-a-123

### Test Case 3: Paste di Pilihan B, C, D
1. Klik input pilihan B/C/D
2. Paste gambar (Ctrl+V)
3. ✅ Gambar muncul di preview masing-masing

### Test Case 4: Multiple Paste
1. Paste gambar di pertanyaan
2. Paste gambar di pilihan A
3. Paste gambar di pilihan B
4. ✅ Semua gambar muncul di preview masing-masing

### Test Case 5: Remove Image
1. Paste gambar di pilihan A
2. Klik tombol X untuk remove
3. ✅ Preview hilang dan hidden input cleared

## Debug Information

Jika masih ada masalah, buka Console (F12) dan check:

```javascript
// Check active element
console.log('Active:', window.pasteImageUploader.activeElement);

// Check container found
console.log('Container:', this.activeElement.closest('.pilihan-item'));

// Check preview container
console.log('Preview:', container.querySelector('[id^="preview-"]'));
```

## File yang Diupdate

```
public/js/paste-image-upload.js
├── init() - Update focus detection
├── handleSuccess() - Update container search strategy
├── showPreview() - Update parent container search
└── removePreview() - Update parent container search
```

## Cara Menggunakan

### Untuk Pertanyaan:
1. Klik area **textarea pertanyaan**
2. Paste (Ctrl+V)
3. ✅ Done!

### Untuk Pilihan Jawaban:
1. Klik **input pilihan** (A/B/C/D)
2. Paste (Ctrl+V)
3. ✅ Done!

### Untuk Pembahasan:
1. Klik area **textarea pembahasan**
2. Paste (Ctrl+V)
3. ✅ Done!

## Status

✅ **FIXED** - Paste image sekarang bekerja di:
- Pertanyaan soal
- Pilihan jawaban A, B, C, D
- Pembahasan
- Sub soal (untuk Benar Salah & MCMA)

---

**Update berhasil!** Sekarang bisa paste gambar di mana saja pada form soal! 🎉
