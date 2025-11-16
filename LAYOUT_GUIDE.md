# Layout Components - Simulasi TKA

## Struktur File Layout

```
resources/views/layouts/
├── styles.blade.php    # CSS global untuk semua halaman
├── sidebar.blade.php   # Komponen sidebar dengan menu navigasi
├── header.blade.php    # Komponen header dengan breadcrumb/search
└── scripts.blade.php   # JavaScript untuk toggle sidebar & submenu
```

## Cara Menggunakan

### 1. Di setiap halaman, tambahkan di bagian `<head>`:

```php
@include('layouts.styles')
```

### 2. Untuk Sidebar:

```php
@include('layouts.sidebar')
```

### 3. Untuk Header:

Ada 2 mode header:

**Mode dengan Search Bar (Dashboard):**
```php
@include('layouts.header', ['pageTitle' => 'Dashboard', 'showSearch' => true])
```

**Mode dengan Breadcrumb (Halaman lain):**
```php
@include('layouts.header', [
    'pageTitle' => 'Buat Soal',
    'breadcrumb' => 'Simulasi TKA'
])
```

### 4. Untuk Scripts (sebelum `</body>`):

```php
@include('layouts.scripts')
```

## Contoh Lengkap

```php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judul Halaman - Simulasi TKA</title>
    @include('layouts.styles')
    <style>
        /* CSS khusus halaman ini */
    </style>
</head>
<body>
    <div class="dashboard-container">
        @include('layouts.sidebar')

        <main class="main-content">
            @include('layouts.header', [
                'pageTitle' => 'Nama Halaman',
                'breadcrumb' => 'Kategori'
            ])

            <div class="content">
                <!-- Konten halaman Anda -->
            </div>
        </main>
    </div>

    @include('layouts.scripts')
</body>
</html>
```

## Fitur Sidebar

- **Auto Active State**: Menu otomatis aktif berdasarkan URL saat ini menggunakan `request()->is()`
- **Submenu Auto Expand**: Submenu otomatis terbuka jika halaman saat ini ada di submenu tersebut
- **Responsive**: Toggle sidebar di mobile view

## Update Konten

Jika ingin mengubah:
- **Logo/Nama Sekolah**: Edit `layouts/sidebar.blade.php`
- **Menu Items**: Edit `layouts/sidebar.blade.php`
- **User Profile**: Edit `layouts/sidebar.blade.php`
- **Global Styles**: Edit `layouts/styles.blade.php`
