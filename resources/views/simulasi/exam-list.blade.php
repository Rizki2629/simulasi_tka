<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ujian - Simulasi TKA</title>
    @include('layouts.styles')
    <style>
        .simulasi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        .simulasi-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            overflow: hidden;
            transition: all 0.3s;
            cursor: pointer;
        }

        .simulasi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: #702637;
        }

        .card-header {
            background: linear-gradient(135deg, #702637 0%, #8b2f47 100%);
            color: white;
            padding: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .card-meta {
            font-size: 13px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .card-body {
            padding: 20px;
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 16px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #666;
        }

        .info-row .material-symbols-outlined {
            font-size: 20px;
            color: #702637;
        }

        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 16px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #702637;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .btn {
            flex: 1;
            padding: 12px 16px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: #702637;
            color: white;
        }

        .btn-primary:hover {
            background: #5a1e2b;
        }

        .btn-outline {
            background: white;
            color: #702637;
            border: 1px solid #702637;
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e0e0e0;
        }

        .empty-state .material-symbols-outlined {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #666;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 24px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        @include('layouts.sidebar')

        <main class="main-content">
            @include('layouts.header', [
                'pageTitle' => 'Daftar Ujian', 
                'breadcrumb' => 'Monitor Siswa',
                'showAvatar' => true,
                'avatarInitials' => 'MD'
            ])

            <div class="content">
                <div style="margin-bottom: 24px;">
                    <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">Daftar Ujian</h2>
                    <p style="color: #666;">Pilih ujian untuk melihat status dan progress siswa</p>
                </div>

                @if($simulasiList->count() > 0)
                <div class="simulasi-grid">
                    @foreach($simulasiList as $simulasi)
                    <div class="simulasi-card">
                        <div class="card-header">
                            <div class="card-title">{{ $simulasi->nama_simulasi }}</div>
                            <div class="card-meta">
                                <span class="material-symbols-outlined" style="font-size: 16px;">subject</span>
                                {{ $simulasi->mataPelajaran->nama }}
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <div class="info-row">
                                    <span class="material-symbols-outlined">schedule</span>
                                    <span>{{ \Carbon\Carbon::parse($simulasi->waktu_mulai)->format('d M Y, H:i') }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="material-symbols-outlined">timer</span>
                                    <span>Durasi: {{ $simulasi->durasi_menit }} menit</span>
                                </div>
                                @php
                                    $now = now();
                                    $start = \Carbon\Carbon::parse($simulasi->waktu_mulai);
                                    $end = \Carbon\Carbon::parse($simulasi->waktu_selesai);
                                    
                                    if ($now < $start) {
                                        $status = 'upcoming';
                                        $statusText = 'Belum Dimulai';
                                    } elseif ($now >= $start && $now <= $end) {
                                        $status = 'active';
                                        $statusText = 'Sedang Berlangsung';
                                    } else {
                                        $status = 'ended';
                                        $statusText = 'Selesai';
                                    }
                                @endphp
                                <div class="info-row">
                                    <span class="material-symbols-outlined">info</span>
                                    <span class="status-badge status-{{ $status }}">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>

                            <div class="stats-row">
                                <div class="stat-item">
                                    <div class="stat-value">{{ $simulasi->participant_count ?? 0 }}</div>
                                    <div class="stat-label">Peserta</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">{{ $simulasi->completed_count ?? 0 }}</div>
                                    <div class="stat-label">Selesai</div>
                                </div>
                            </div>

                            <div class="card-actions">
                                <a href="{{ route('simulasi.student.status', $simulasi->id) }}" class="btn btn-primary">
                                    <span class="material-symbols-outlined">visibility</span>
                                    Lihat Status Siswa
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <span class="material-symbols-outlined">quiz</span>
                    <h3>Belum Ada Ujian</h3>
                    <p>Belum ada ujian yang dibuat. Klik tombol di bawah untuk membuat ujian baru.</p>
                    <a href="{{ route('simulasi.generate') }}" class="btn btn-primary" style="display: inline-flex;">
                        <span class="material-symbols-outlined">add</span>
                        Buat Ujian Baru
                    </a>
                </div>
                @endif
            </div>
        </main>
    </div>

    @include('layouts.scripts')
</body>
</html>
