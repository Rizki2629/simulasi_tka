@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header Hasil -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Ujian Selesai!</h1>
                <p class="text-gray-600">{{ $hasilUjian['nama_simulasi'] }}</p>
                <p class="text-sm text-gray-500">{{ $hasilUjian['mata_pelajaran'] }}</p>
            </div>

            <!-- Nilai Besar -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-8 text-white text-center mb-6">
                <p class="text-lg mb-2">Nilai Anda</p>
                <p class="text-6xl font-bold mb-2">{{ number_format($hasilUjian['nilai_total'], 0) }}</p>
                <p class="text-sm opacity-90">dari {{ $hasilUjian['jumlah_soal'] }} soal</p>
            </div>

            <!-- Statistik -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <svg class="w-8 h-8 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-2xl font-bold text-green-600">{{ $hasilUjian['jumlah_benar'] }}</p>
                    <p class="text-sm text-gray-600">Benar</p>
                </div>
                
                <div class="bg-red-50 rounded-lg p-4 text-center">
                    <svg class="w-8 h-8 text-red-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <p class="text-2xl font-bold text-red-600">{{ $hasilUjian['jumlah_salah'] }}</p>
                    <p class="text-sm text-gray-600">Salah</p>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <svg class="w-8 h-8 text-blue-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-2xl font-bold text-blue-600">{{ $hasilUjian['jumlah_soal'] }}</p>
                    <p class="text-sm text-gray-600">Total Soal</p>
                </div>
            </div>

            <!-- Detail Jawaban per Soal -->
            <div class="border-t pt-6">
                <h3 class="text-xl font-semibold mb-4">Detail Jawaban</h3>
                <div class="space-y-3">
                    @foreach($hasilUjian['detail_jawaban'] as $index => $detail)
                        <div class="flex items-center justify-between p-4 rounded-lg {{ $detail['nilai'] > 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if($detail['nilai'] > 0)
                                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">Soal #{{ $index + 1 }}</p>
                                    <p class="text-sm text-gray-600">
                                        Jenis: 
                                        @if($detail['jenis_soal'] == 'pilihan_ganda')
                                            Pilihan Ganda
                                        @elseif($detail['jenis_soal'] == 'benar_salah')
                                            Benar Salah
                                        @elseif($detail['jenis_soal'] == 'mcma')
                                            Pilihan Ganda Kompleks
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $detail['nilai'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $detail['nilai'] }} / {{ $detail['maksimal'] }}
                                </p>
                                <p class="text-xs text-gray-500">Poin</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 mt-6">
                <a href="{{ route('simulasi.student.dashboard') }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg text-center transition">
                    Kembali ke Dashboard
                </a>
                <a href="{{ route('simulasi.detail.nilai', $hasilUjian['nilai_id']) }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg text-center transition">
                    Lihat Pembahasan
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
