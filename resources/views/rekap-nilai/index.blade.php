<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Nilai - Simulasi TKA</title>
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
            padding: 24px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .simulasi-card:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            transform: translateY(-4px);
            border-color: #702637;
        }

        .simulasi-header {
            margin-bottom: 16px;
        }

        .simulasi-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .simulasi-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #666;
        }

        .simulasi-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .simulasi-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f0f0f0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #702637;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
        }

        .empty-text {
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        @include('layouts.sidebar')

        <main class="main-content">
            @include('layouts.header', [
                'pageTitle' => 'Rekap Nilai', 
                'breadcrumb' => 'Daftar Ujian',
                'showAvatar' => true,
                'avatarInitials' => 'MD'
            ])

            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Rekap Nilai Ujian</h1>
                    <p class="page-subtitle">Pilih ujian untuk melihat nilai siswa yang telah mengerjakan.</p>
                </div>

                @if($simulasiList->count() > 0)
                    <div class="simulasi-grid">
                        @foreach($simulasiList as $simulasi)
                        <a href="{{ route('rekap-nilai.show', $simulasi->id) }}" class="simulasi-card">
                            <div class="simulasi-header">
                                <h3 class="simulasi-title">{{ $simulasi->nama_simulasi }}</h3>
                                <div class="simulasi-meta">
                                    <span class="simulasi-meta-item">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">calendar_today</span>
                                        {{ $simulasi->created_at->format('d M Y') }}
                                    </span>
                                    <span class="simulasi-meta-item">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">schedule</span>
                                        {{ $simulasi->durasi_menit }} menit
                                    </span>
                                    @if($simulasi->mataPelajaran)
                                    <span class="badge badge-info">
                                        {{ $simulasi->mataPelajaran->nama_mata_pelajaran }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="simulasi-stats">
                                <div class="stat-item">
                                    <div class="stat-label">Peserta</div>
                                    <div class="stat-value">{{ $simulasi->total_peserta }}</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Rata-rata</div>
                                    <div class="stat-value">{{ number_format($simulasi->rata_rata, 1) }}</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Tertinggi</div>
                                    <div class="stat-value">{{ number_format($simulasi->nilai_tertinggi, 1) }}</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Terendah</div>
                                    <div class="stat-value">{{ number_format($simulasi->nilai_terendah, 1) }}</div>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    @if($simulasiList->hasPages())
                    <div style="margin-top: 32px; display: flex; justify-content: center;">
                        {{ $simulasiList->links('vendor.pagination.custom') }}
                    </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <span class="material-symbols-outlined" style="font-size: inherit;">quiz</span>
                        </div>
                        <div class="empty-title">Belum Ada Ujian</div>
                        <div class="empty-text">Buat ujian terlebih dahulu di menu Simulasi TKA</div>
                    </div>
                @endif
            </div>
        </main>
    </div>

    @include('layouts.scripts')
</body>
</html>
