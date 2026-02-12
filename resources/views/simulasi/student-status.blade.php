@extends('layouts.app')

@section('title', 'Status Siswa - ' . $simulasi->nama_simulasi)
@php
    $pageTitle = 'Status Siswa';
    $breadcrumb = $simulasi->nama_simulasi;
    $showAvatar = false;
    $showSearch = false;
@endphp

@push('styles')
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

        .simulasi-info {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #f0f0f0;
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }

        .info-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .info-meta {
            color: #666;
            font-size: 14px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

       .status-upcoming {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-ended {
            background: #fee2e2;
            color: #991b1b;
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

        .filter-dropdown {
            position: relative;
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
        }

        .btn:hover {
            background: #f9fafb;
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

        .student-status-badge {
            display: inline-block;
            padding: 6px 12px;
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

        .badge-secondary {
            background: #f3f4f6;
            color: #6b7280;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
            border: 1px solid;
            background: white;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .btn-danger {
            color: #dc2626;
            border-color: #dc2626;
        }

        .btn-danger:hover {
            background: #dc2626;
            color: white;
        }

        .btn-warning {
            color: #d97706;
            border-color: #d97706;
        }

        .btn-warning:hover {
            background: #d97706;
            color: white;
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
    </style>
@endpush

@section('content')
    <div class="content">
                <a href="{{ route('simulasi.exam.list') }}" class="back-btn">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Kembali ke Daftar Ujian
                </a>

                <div class="simulasi-info">
                    <div class="info-header">
                        <div>
                            <h2 class="info-title">{{ $simulasi->nama_simulasi }}</h2>
                            <p class="info-meta">
                                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">subject</span>
                                {{ $simulasi->mataPelajaran->nama }} • 
                                {{ \Carbon\Carbon::parse($simulasi->waktu_mulai)->format('d M Y, H:i') }}
                            </p>
                        </div>
                        @php
                            $now = now();
                            $start = \Carbon\Carbon::parse($simulasi->waktu_mulai);
                            $end = \Carbon\Carbon::parse($simulasi->waktu_selesai);
                            
                            if ($now < $start) {
                                $statusClass = 'upcoming';
                                $statusText = 'Belum Dimulai';
                            } elseif ($now >= $start && $now <= $end) {
                                $statusClass = 'active';
                                $statusText = 'Sedang Berlangsung';
                            } else {
                                $statusClass = 'ended';
                                $statusText = 'Selesai';
                            }
                        @endphp
                        <span class="status-badge status-{{ $statusClass }}">{{ $statusText }}</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Siswa</h3>
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
                                    <a href="{{ route('simulasi.student.status', $simulasi->id) }}" class="{{ !request('class_filter') ? 'active' : '' }}">Semua Kelas</a>
                                    @foreach($classes as $class)
                                        <a href="{{ route('simulasi.student.status', ['simulasi' => $simulasi->id, 'class_filter' => $class]) }}" 
                                           class="{{ request('class_filter') == $class ? 'active' : '' }}">
                                            Kelas {{ $class }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Siswa</th>
                                    <th>NISN</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Sisa Waktu</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $filteredData = $studentData;
                                    if (request('class_filter')) {
                                        $filteredData = $studentData->filter(function($data) {
                                            return $data->student->rombongan_belajar == request('class_filter');
                                        });
                                    }
                                @endphp
                                
                                @forelse($filteredData as $index => $data)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div style="font-weight: 600;">{{ $data->student->name }}</div>
                                        <div style="font-size: 12px; color: #999;">{{ $data->student->email }}</div>
                                    </td>
                                    <td>{{ $data->student->nisn ?? '-' }}</td>
                                    <td>{{ $data->student->rombongan_belajar ?? '-' }}</td>
                                    <td>
                                        <span class="student-status-badge badge-{{ $data->statusColor }}">
                                            {{ $data->statusText }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $waktuMulai = data_get($data->session, 'started_at');
                                            if (!$waktuMulai && $data->peserta) {
                                                $wm = data_get($data->peserta, 'waktu_mulai');
                                                if ($wm instanceof \Carbon\Carbon) {
                                                    $waktuMulai = $wm;
                                                } elseif ($wm) {
                                                    try { $waktuMulai = \Carbon\Carbon::parse($wm); } catch (\Throwable $e) {}
                                                }
                                            }
                                        @endphp
                                        {{ $waktuMulai ? $waktuMulai->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td>{{ $data->nilai ? $data->nilai->created_at->format('d M Y, H:i') : '-' }}</td>
                                    <td>
                                        @php
                                            $sisaWaktu = '-';
                                            $sisaClass = '';
                                            if ($waktuMulai && !$data->nilai) {
                                                $batasSelesai = $waktuMulai->copy()->addMinutes((int) $simulasi->durasi_menit);
                                                $now = now();
                                                if ($now->lt($batasSelesai)) {
                                                    $diff = $now->diff($batasSelesai);
                                                    $sisaJam = $diff->h;
                                                    $sisaMenit = $diff->i;
                                                    $sisaDetik = $diff->s;
                                                    if ($sisaJam > 0) {
                                                        $sisaWaktu = "{$sisaJam}j {$sisaMenit}m";
                                                    } else {
                                                        $sisaWaktu = "{$sisaMenit}m {$sisaDetik}d";
                                                    }
                                                    $sisaClass = $sisaMenit <= 10 && $sisaJam == 0 ? 'badge-warning' : 'badge-success';
                                                } else {
                                                    $sisaWaktu = 'Habis';
                                                    $sisaClass = 'badge-danger';
                                                }
                                            } elseif ($data->nilai) {
                                                $sisaWaktu = 'Selesai';
                                                $sisaClass = 'badge-success';
                                            }
                                        @endphp
                                        @if($sisaWaktu !== '-')
                                            <span class="student-status-badge {{ $sisaClass }}">{{ $sisaWaktu }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-btns" style="justify-content: center;">
                                            @if($data->session || $data->nilai)
                                            <button class="btn-sm btn-warning" onclick="resetLogin({{ $simulasi->id }}, {{ $data->student->id }}, '{{ $data->student->name }}')">
                                                <span class="material-symbols-outlined" style="font-size: 14px;">logout</span>
                                                Reset Login
                                            </button>
                                            @endif
                                            
                                            @if($data->nilai)
                                            <button class="btn-sm btn-danger" onclick="resetProgress({{ $simulasi->id }}, {{ $data->student->id }}, '{{ $data->student->name }}')">
                                                <span class="material-symbols-outlined" style="font-size: 14px;">restart_alt</span>
                                                Reset Progress
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                                        Tidak ada siswa{{ request('class_filter') ? ' di kelas ' . request('class_filter') : '' }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

        function resetLogin(simulasiId, userId, studentName) {
            if (!confirm(`Reset login untuk ${studentName}?\n\nSiswa akan logout dan harus login ulang. Progress ujian tetap tersimpan.`)) {
                return;
            }

            fetch(`/simulasi/${simulasiId}/reset-login/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal reset login: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat reset login');
            });
        }

        function resetProgress(simulasiId, userId, studentName) {
            if (!confirm(`PERINGATAN: Reset progress untuk ${studentName}?\n\nSemua jawaban dan nilai akan dihapus. Siswa dapat mengulang ujian dari awal.\n\nApakah Anda yakin?`)) {
                return;
            }

            fetch(`/simulasi/${simulasiId}/reset-progress/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal reset progress: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat reset progress');
            });
        }
    </script>
@endpush
