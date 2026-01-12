# Migrasi SQLite → Firestore (Firebase)

Dokumen ini menyiapkan migrasi bertahap dari SQLite (Laravel/Eloquent) ke Firestore.

## 1) Prasyarat

- Punya Firebase project + Firestore aktif.
- Buat Service Account JSON di Firebase Console.
- Simpan file JSON di komputer (jangan di-commit).

## 2) Konfigurasi `.env`

Set minimal ini:

- `FIREBASE_PROJECT_ID` = Project ID Firebase
- `FIREBASE_CREDENTIALS` = path file service account JSON
  - Boleh absolute path Windows (contoh `C:\\keys\\service-account.json`)
  - Boleh relative ke root project (contoh `storage/app/firebase.json`)
- `FIRESTORE_DATABASE_ID` = nama database Firestore (default: kosong / `(default)`)

Catatan: selama migrasi sebaiknya session/cache/queue tidak pakai DB SQL.

## 3) Sync data dari SQLite ke Firestore

### Cek jumlah data (tanpa menulis)

```bash
php artisan firestore:sync-from-sqlite --tables=users,simulasi,tokens --limit=5 --dry-run
```

### Sync beberapa tabel (upsert by field `id`)

```bash
php artisan firestore:sync-from-sqlite --tables=users,simulasi,tokens --mode=upsert
```

### Sync semua tabel

```bash
php artisan firestore:sync-from-sqlite --mode=upsert
```

## 4) Tentang format dokumen

- Document ID: auto-generated oleh Firestore.
- Field `id`: tetap disimpan dari kolom SQLite (atau fallback) agar bisa query/update by id.

## 5) Next step (porting aplikasi)

Sync ini baru memindahkan data. Agar aplikasi benar-benar memakai Firestore, code yang masih `Eloquent/DB::table()` perlu dipindahkan ke repository Firestore secara bertahap.
