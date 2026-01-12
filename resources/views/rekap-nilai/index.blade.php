@extends('layouts.app')

@section('title', 'Rekap Nilai - Simulasi TKA')

@php
    $pageTitle = 'Rekap Nilai';
    $breadcrumb = 'Daftar Ujian';
    $showAvatar = false;
    $showSearch = false;
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <style>
        .content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 14px;
        }

        .table-wrap {
            margin-top: 18px;
            border: 1px solid #f0f0f0;
            border-radius: 14px;
            overflow: hidden;
            background: white;
        }

        .table-scroll {
            overflow-x: auto;
        }

        table.rekap-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1100px;
        }

        .rekap-table thead th {
            background: #702637;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            padding: 12px 14px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 1;
            white-space: nowrap;
        }

        .rekap-table thead th:first-child {
            border-top-left-radius: 14px;
        }

        .rekap-table thead th:last-child {
            border-top-right-radius: 14px;
        }

        .rekap-table thead th.num {
            text-align: right;
        }

        .rekap-table tbody td {
            padding: 14px 14px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
            background: #fff;
        }

        .rekap-table tbody tr:nth-child(even) td {
            background: #fbfbfb;
        }

        .rekap-table tbody tr:hover td {
            background: #f9fafb;
        }

        .rekap-table tbody tr:hover td:first-child {
            box-shadow: inset 4px 0 0 #702637;
        }

        .rekap-table tbody tr:last-child td {
            border-bottom: none;
        }

        .rekap-table td.num {
            text-align: right;
            white-space: nowrap;
        }

        .rekap-table td.action {
            text-align: right;
            width: 56px;
        }

        .rekap-link {
            text-decoration: none;
            color: inherit;
        }

        .simulasi-name {
            font-weight: 800;
            color: #111;
            line-height: 1.2;
        }

        .simulasi-sub {
            margin-top: 6px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 12px;
            color: #666;
        }

        .sub-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .metric {
            text-align: right;
        }

        .metric .value {
            font-size: 16px;
            font-weight: 800;
            color: #702637;
            line-height: 1.1;
        }

        .metric .label {
            font-size: 11px;
            color: #888;
            margin-top: 3px;
        }

        .extremes {
            font-size: 12px;
            color: #666;
            line-height: 1.35;
        }

        .extremes strong {
            color: #702637;
            font-weight: 800;
        }

        .chev {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            color: #999;
            background: #fff;
            border: 1px solid #f0f0f0;
        }

        .rekap-table tbody tr:hover .chev {
            border-color: #702637;
            background: #702637;
            color: #fff;
        }

        /* DataTables controls (match existing accent color) */
        .dt-container {
            padding: 0;
        }

        .dt-layout-row {
            padding: 10px 14px;
        }

        .dt-layout-row:first-child {
            border-bottom: 1px solid #f0f0f0;
        }

        .dt-layout-row:last-child {
            border-top: 1px solid #f0f0f0;
        }

        .dt-search input,
        .dt-length select {
            border: 1px solid #f0f0f0;
            border-radius: 10px;
            padding: 8px 10px;
            outline: none;
            background: #fff;
        }

        .dt-search input:focus,
        .dt-length select:focus {
            border-color: #702637;
        }

        .dt-paging .dt-paging-button {
            border-radius: 10px;
        }

        .dt-paging .dt-paging-button.current,
        .dt-paging .dt-paging-button.current:hover {
            background: #702637 !important;
            color: #fff !important;
            border-color: #702637 !important;
        }

        .dt-paging .dt-paging-button:hover {
            border-color: #702637 !important;
            color: #702637 !important;
            background: #fff !important;
        }

        @media (max-width: 768px) {
            .content {
                max-width: none;
            }

            table.rekap-table {
                min-width: 900px;
            }
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
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('rekapNilaiTable');
            if (!el || typeof DataTable === 'undefined') return;

            new DataTable(el, {
                scrollX: true,
                pageLength: 10,
                order: [], // keep server order (created_at desc)
                columnDefs: [
                    { targets: [1, 2], className: 'dt-body-right' },
                    { targets: [5], orderable: false, searchable: false },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/id.json'
                }
            });
        });
    </script>
@endpush

@section('content')
    <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Rekap Nilai Ujian</h1>
                    <p class="page-subtitle">Pilih ujian untuk melihat nilai siswa yang telah mengerjakan.</p>
                </div>

                @if($simulasiList->count() > 0)
                    <div class="table-wrap">
                        <div class="table-scroll">
                            <table id="rekapNilaiTable" class="rekap-table">
                                <thead>
                                    <tr>
                                        <th>Ujian</th>
                                        <th class="num">Peserta</th>
                                        <th class="num">Rata-rata</th>
                                        <th>Tertinggi</th>
                                        <th>Terendah</th>
                                        <th class="num">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($simulasiList as $simulasi)
                                        @php
                                            $top = $simulasi->top_nilai ?? null;
                                            $bottom = $simulasi->bottom_nilai ?? null;
                                            $topName = $top && $top->user ? $top->user->name : null;
                                            $topClass = $top && $top->user ? ($top->user->rombongan_belajar ?? null) : null;
                                            $bottomName = $bottom && $bottom->user ? $bottom->user->name : null;
                                            $bottomClass = $bottom && $bottom->user ? ($bottom->user->rombongan_belajar ?? null) : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a class="rekap-link" href="{{ route('rekap-nilai.show', $simulasi->id) }}">
                                                    <div class="simulasi-name">{{ $simulasi->nama_simulasi }}</div>
                                                    <div class="simulasi-sub">
                                                        <span class="sub-item">
                                                            <span class="material-symbols-outlined" style="font-size: 16px;">menu_book</span>
                                                            <span>{{ $simulasi->mataPelajaran->nama_mata_pelajaran ?? 'Umum' }}</span>
                                                        </span>
                                                        <span class="sub-item">
                                                            <span class="material-symbols-outlined" style="font-size: 16px;">calendar_today</span>
                                                            <span>{{ $simulasi->created_at->format('d M Y') }}</span>
                                                        </span>
                                                        <span class="sub-item">
                                                            <span class="material-symbols-outlined" style="font-size: 16px;">schedule</span>
                                                            <span>{{ $simulasi->durasi_menit }} menit</span>
                                                        </span>
                                                    </div>
                                                </a>
                                            </td>
                                            <td class="num">
                                                <div class="metric">
                                                    <div class="value">{{ $simulasi->total_peserta }}</div>
                                                    <div class="label">Peserta</div>
                                                </div>
                                            </td>
                                            <td class="num">
                                                <div class="metric">
                                                    <div class="value">{{ number_format($simulasi->rata_rata, 1) }}</div>
                                                    <div class="label">Rata-rata</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="extremes">
                                                    <strong>{{ number_format($simulasi->nilai_tertinggi, 1) }}</strong>
                                                    @if($topName)
                                                        — {{ $topName }}@if($topClass) ({{ $topClass }})@endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="extremes">
                                                    <strong>{{ number_format($simulasi->nilai_terendah, 1) }}</strong>
                                                    @if($bottomName)
                                                        — {{ $bottomName }}@if($bottomClass) ({{ $bottomClass }})@endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="action">
                                                <a href="{{ route('rekap-nilai.show', $simulasi->id) }}" class="chev" aria-label="Lihat rekap">
                                                    <span class="material-symbols-outlined" style="font-size: 18px;">chevron_right</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
    @endsection
