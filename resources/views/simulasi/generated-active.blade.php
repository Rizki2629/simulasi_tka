@extends('layouts.app')

@section('title', 'Daftar Simulasi Aktif - Simulasi TKA')

@php
    $pageTitle = 'Daftar Simulasi Aktif';
    $breadcrumb = 'Simulasi TKA';
    $showAvatar = false;
    $showSearch = false;
@endphp

@push('styles')
    <style>
        .simulasi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        .simulasi-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            overflow: hidden;
            transition: all 0.25s;
        }

        .simulasi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
            border-color: #702637;
        }

        .card-header {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            padding: 18px 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .card-subtitle {
            font-size: 13px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .card-body {
            padding: 18px 20px;
        }

        .info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            color: #4b5563;
            font-size: 14px;
            margin-bottom: 14px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-row .material-symbols-outlined {
            font-size: 20px;
            color: #702637;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #702637;
            color: white;
        }

        .btn-primary:hover {
            background: #5f1f2f;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #111827;
            border-color: #e5e7eb;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .empty {
            background: white;
            border: 1px dashed #e5e7eb;
            border-radius: 16px;
            padding: 28px;
            margin-top: 24px;
            color: #6b7280;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .topbar small {
            color: #6b7280;
        }
    </style>
@endpush

@section('content')
    <div style="padding: 24px;">
        <div class="topbar">
            <div>
                <div style="font-size: 14px; color: #6b7280;">Simulasi yang ditandai aktif (belum dihentikan). Jika sudah selesai, hentikan agar bisa generate ulang paket yang sama.</div>
                <small>Waktu server: {{ $now->format('d/m/Y H:i') }}</small>
            </div>
            <a href="{{ route('simulasi.generate') }}" class="btn btn-primary">
                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                Generate Simulasi
            </a>
        </div>

        @if($simulasis->isEmpty())
            <div class="empty">
                <div style="font-weight: 800; color: #111827; margin-bottom: 6px;">Belum ada simulasi yang ditandai aktif</div>
                <div>Silakan buat simulasi dari menu <b>Generate Simulasi</b>.</div>
            </div>
        @else
            <div class="simulasi-grid">
                @foreach($simulasis as $sim)
                    @php
                        $paket = $sim->simulasiSoal->first()?->soal;
                        $paketKode = $paket?->kode_soal ?? '-';
                        $paketJumlah = (int) ($paket?->sub_soal_count ?? 0);
                        $mapel = $sim->mataPelajaran?->nama ?? '-';
                        $mulai = $sim->waktu_mulai ? $sim->waktu_mulai->format('d/m/Y H:i') : '-';
                        $selesai = $sim->waktu_selesai ? $sim->waktu_selesai->format('d/m/Y H:i') : '-';
                    @endphp

                    <div class="simulasi-card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">{{ $sim->nama_simulasi }}</div>
                                <div class="card-subtitle">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">menu_book</span>
                                    <span>{{ $mapel }}</span>
                                </div>
                            </div>
                            @php
                                $status = 'Berjalan';
                                $icon = 'play_circle';
                                if ($sim->waktu_mulai && $now->lt($sim->waktu_mulai)) {
                                    $status = 'Belum Mulai';
                                    $icon = 'schedule';
                                } elseif ($sim->waktu_selesai && $now->gt($sim->waktu_selesai)) {
                                    $status = 'Selesai';
                                    $icon = 'task_alt';
                                }
                            @endphp
                            <div class="badge">
                                <span class="material-symbols-outlined" style="font-size: 16px;">{{ $icon }}</span>
                                {{ $status }}
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="info">
                                <div class="info-row">
                                    <span class="material-symbols-outlined">schedule</span>
                                    <span><b>{{ $mulai }}</b> s/d <b>{{ $selesai }}</b></span>
                                </div>
                                <div class="info-row">
                                    <span class="material-symbols-outlined">timer</span>
                                    <span>Durasi: <b>{{ (int) $sim->durasi_menit }}</b> menit</span>
                                </div>
                                <div class="info-row">
                                    <span class="material-symbols-outlined">quiz</span>
                                    <span>Paket: <b>{{ $paketKode }}</b> ({{ $paketJumlah }} soal)</span>
                                </div>
                                <div class="info-row">
                                    <span class="material-symbols-outlined">group</span>
                                    <span>Peserta terdaftar: <b>{{ (int) $sim->simulasi_peserta_count }}</b></span>
                                </div>
                            </div>

                            <div class="actions">
                                <a class="btn btn-secondary" href="{{ route('simulasi.student.status', $sim->id) }}">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">monitor_heart</span>
                                    Monitor
                                </a>
                                <a class="btn btn-secondary" href="{{ route('rekap-nilai.show', $sim->id) }}">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">grading</span>
                                    Rekap Nilai
                                </a>
                                <form action="{{ route('simulasi.stop', $sim->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hentikan simulasi ini? Setelah dihentikan, Anda bisa generate ulang paket yang sama.');">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary" style="color: #b91c1c; border-color: #fecaca; background: #fff5f5;">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
