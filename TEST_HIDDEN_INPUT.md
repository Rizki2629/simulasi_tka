# Test Hidden Input untuk Paste Image

## Cara Test di Browser Console

### 1. Sebelum Submit Form
Setelah paste gambar, jalankan ini di Browser Console (F12 → Console):

```javascript
// Cek semua hidden input gambar
console.log('=== CHECKING HIDDEN INPUTS ===');
const form = document.getElementById('formSoal');
if (form) {
    const hiddenInputs = form.querySelectorAll('input[type="hidden"][name*="gambar"]');
    console.log('Total hidden inputs with "gambar":', hiddenInputs.length);
    hiddenInputs.forEach(input => {
        console.log(`${input.name} = ${input.value}`);
    });
    
    // Cek apakah hidden input berada di dalam form
    console.log('\n=== FORM CHECK ===');
    console.log('Form ID:', form.id);
    console.log('Form action:', form.action);
    console.log('All inputs in form:', form.querySelectorAll('input').length);
} else {
    console.error('Form tidak ditemukan!');
}
```

### 2. Expected Output
Seharusnya muncul output seperti:
```
=== CHECKING HIDDEN INPUTS ===
Total hidden inputs with "gambar": 1
gambar_soal_1 = soal_images/paste_1764490314_692bfc4a899ff.png

=== FORM CHECK ===
Form ID: formSoal
Form action: http://127.0.0.1:8000/soal/1
All inputs in form: 10
```

### 3. Jika Hidden Input Tidak Ada
Jika tidak muncul hidden input, cek apakah paste berhasil:
```javascript
// Cek apakah paste image handler berjalan
console.log('Paste image uploader:', typeof PasteImageUploader);
console.log('Active element:', document.activeElement);
```

### 4. Setelah Submit
Gunakan Tinker untuk cek nilai yang tersimpan di database:

```bash
php artisan tinker
```

Lalu jalankan:

```php
App\Models\Soal::latest()->first();
```

## Expected Results

### ✅ Success Indicators:
1. Hidden input terdeteksi dengan nama `gambar_soal_1`, `gambar_pilihan_1_a`, atau `gambar_pernyataan_1_2`
2. Value berisi path seperti `soal_images/paste_xxxxx.png`
3. Hidden input berada di dalam `<form id="formSoal">`
4. Setelah submit, hasil query Tinker menampilkan path gambar di database
5. File exists: YES

### ❌ Failure Indicators:
1. Hidden input tidak ditemukan → Paste handler gagal
2. Hidden input di luar form → JavaScript append ke tempat yang salah
3. Value empty → Path tidak diset
4. Database NULL → Form tidak mengirim hidden input
5. File exists: NO → File tidak tersimpan atau path salah

## Quick Debug Commands

### Cek File Upload Terakhir
```powershell
Get-ChildItem "storage\app\public\soal_images" | Sort-Object LastWriteTime -Descending | Select-Object -First 5 Name, LastWriteTime
```

### Cek Database
```powershell
php artisan tinker
```

### Cek Laravel Log
```powershell
Get-Content "storage\logs\laravel.log" -Tail 20
```
