@extends('layouts.app')

@section('title', $simulasi->nama_simulasi . ' - Rekap Nilai')

@php
    $pageTitle = $simulasi->nama_simulasi;
    $breadcrumb = 'Rekap Nilai';
    $showAvatar = false;
    $showSearch = false;
@endphp

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #702637;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 16px;
            transition: all 0.2s;
        }

        .back-btn:hover {
            gap: 12px;
        }

        .simulasi-info-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            padding: 24px;
            margin-bottom: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 16px;
        }

        .stat-card {
            text-align: center;
            padding: 16px;
            background: #f9fafb;
            border-radius: 12px;
        }

        .stat-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #702637;
        }

        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            overflow: hidden;
        }

        .card-header {
            padding: 24px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
        }

        .card-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f9fafb;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            min-width: 300px;
        }

        .search-box input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn:hover {
            background: #f9fafb;
        }

        .btn-primary {
            background: #702637;
            color: white;
            border-color: #702637;
        }

        .btn-primary:hover {
            background: #5a1e2b;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .btn-info {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .btn-info:hover {
            background: #2563eb;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .btn-success:hover {
            background: #059669;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
        }

        th {
            padding: 16px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
        }

        td {
            padding: 20px;
            border-bottom: 1px solid #f9fafb;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fed7aa;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .score-cell {
            font-size: 18px;
            font-weight: 700;
        }

        .score-high {
            color: #059669;
        }

        .score-medium {
            color: #d97706;
        }

        .score-low {
            color: #dc2626;
        }

        .rank-badge {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .rank-1 {
            background: #fcd34d;
            color: #78350f;
        }

        .rank-2 {
            background: #d1d5db;
            color: #374151;
        }

        .rank-3 {
            background: #fed7aa;
            color: #92400e;
        }

        .filter-dropdown {
            position: relative;
        }

        .filter-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            width: 200px;
            z-index: 50;
            padding: 8px 0;
            margin-top: 4px;
        }

        .filter-menu a {
            display: block;
            padding: 8px 16px;
            color: #4b5563;
            text-decoration: none;
            font-size: 14px;
        }

        .filter-menu a:hover {
            background: #f3f4f6;
        }

        .filter-menu a.active {
            background: #f3f4f6;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="content">
                <a href="{{ route('rekap-nilai.index') }}" class="back-btn">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Kembali ke Daftar Ujian
                </a>

                <div class="simulasi-info-card">
                    <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">{{ $simulasi->nama_simulasi }}</h2>
                    <p style="color: #666; margin-bottom: 16px;">{{ $simulasi->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Total Peserta</div>
                            <div class="stat-value">{{ $nilaiList->total() }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Rata-rata Nilai</div>
                            <div class="stat-value">{{ number_format($nilaiList->avg('nilai_total'), 1) }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Nilai Tertinggi</div>
                            <div class="stat-value">{{ number_format($nilaiList->max('nilai_total'), 1) }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Nilai Terendah</div>
                            <div class="stat-value">{{ number_format($nilaiList->min('nilai_total'), 1) }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Nilai Siswa</h3>
                        <div class="card-actions">
                            <div class="search-box">
                                <span class="material-symbols-outlined">search</span>
                                <input type="text" placeholder="Cari siswa..." id="searchInput" onkeyup="searchTable()">
                            </div>
                            
                            <div class="filter-dropdown">
                                <button class="btn" onclick="toggleFilterMenu()">
                                    <span class="material-symbols-outlined">tune</span>
                                    Filter Kelas
                                </button>
                                <div id="filterMenu" class="filter-menu">
                                    <a href="{{ route('rekap-nilai.show', $simulasi->id) }}" class="{{ !request('class_filter') ? 'active' : '' }}">Semua Kelas</a>
                                    @foreach($classes as $class)
                                        <a href="{{ route('rekap-nilai.show', ['simulasi' => $simulasi->id, 'class_filter' => $class]) }}" 
                                           class="{{ request('class_filter') == $class ? 'active' : '' }}">
                                            Kelas {{ $class }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <a href="{{ route('rekap-nilai.export') }}" class="btn btn-primary">
                                <span class="material-symbols-outlined">download</span>
                                Export Excel
                            </a>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Rank</th>
                                    <th>Nama Siswa</th>
                                    <th>NISN</th>
                                    <th>Kelas</th>
                                    <th>Tanggal Mengerjakan</th>
                                    <th>Waktu Mengerjakan</th>
                                    <th>Total Soal</th>
                                    <th>Nilai</th>
                                    <th style="width: 200px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nilaiList as $index => $nilai)
                                <tr>
                                    <td>
                                        <div class="rank-badge {{ $nilai->nilai_total >= 70 && $index + $nilaiList->firstItem() <= 3 ? 'rank-' . ($index + $nilaiList->firstItem()) : '' }}">
                                            {{ $index + $nilaiList->firstItem() }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;">{{ $nilai->user->name }}</div>
                                        <div style="font-size:12px; color: #999;">{{ $nilai->user->email }}</div>
                                    </td>
                                    <td>{{ $nilai->user->nisn ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $nilai->user->rombongan_belajar ?? '-' }}</span>
                                    </td>
                                    <td>{{ $nilai->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        @php
                                            $peserta = $pesertaByUserId[$nilai->user_id] ?? null;
                                            $mulai = $peserta?->waktu_mulai;
                                            $selesai = $peserta?->waktu_selesai;

                                            $durSeconds = null;
                                            if ($mulai && $selesai) {
                                                $durSeconds = $mulai->diffInSeconds($selesai);
                                            }

                                            if ($durSeconds === null) {
                                                echo '-';
                                            } else {
                                                $m = intdiv($durSeconds, 60);
                                                $s = $durSeconds % 60;
                                                echo $m . ' menit ' . $s . ' detik';
                                            }
                                        @endphp
                                    </td>
                                    <td>{{ $totalSoalSimulasi ?? $nilai->jumlah_soal }}</td>
                                    <td>
                                        <span class="score-cell {{ $nilai->nilai_total >= 70 ? 'score-high' : ($nilai->nilai_total >= 50 ? 'score-medium' : 'score-low') }}">
                                            {{ number_format($nilai->nilai_total, 1) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                            <a href="{{ route('rekap-nilai.review', $nilai->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Lihat detail jawaban siswa"
                                               style="padding: 6px 12px; font-size: 13px; white-space: nowrap;">
                                                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">visibility</span>
                                                Lihat Review
                                            </a>
                                            <a href="{{ route('rekap-nilai.download', $nilai->id) }}" 
                                               class="btn btn-sm btn-success" 
                                               title="Download hasil pekerjaan siswa"
                                               style="padding: 6px 12px; font-size: 13px; white-space: nowrap;">
                                                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">download</span>
                                                Download
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                                        Belum ada siswa yang mengerjakan ujian ini
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($nilaiList->count() > 0)
                    <div style="padding: 20px; display: flex; justify-content: center;">
                        {{ $nilaiList->appends(request()->query())->links('vendor.pagination.custom') }}
                    </div>
                    @endif
                </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleFilterMenu() {
            const menu = document.getElementById('filterMenu');
            menu.style.display = menu.style.display === 'none' || menu.style.display === '' ? 'block' : 'none';
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('filterMenu');
            const btn = event.target.closest('.filter-dropdown');
            if (menu && !btn) {
                menu.style.display = 'none';
            }
        });

        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }
    </script>
@endpush
