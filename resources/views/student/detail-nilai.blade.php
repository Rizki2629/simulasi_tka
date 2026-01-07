@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $nilai->simulasi->nama_simulasi }}</h1>
                    <p class="text-gray-600">{{ $nilai->mataPelajaran->nama_mata_pelajaran }}</p>
                    <p class="text-sm text-gray-500">{{ $nilai->created_at->format('d F Y, H:i') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 mb-1">Nilai Total</p>
                    <p class="text-4xl font-bold text-blue-600">{{ number_format($nilai->nilai_total, 0) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $nilai->jumlah_soal }}</p>
                    <p class="text-sm text-gray-600">Total Soal</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $nilai->jumlah_benar }}</p>
                    <p class="text-sm text-gray-600">Jawaban Benar</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $nilai->jumlah_salah }}</p>
                    <p class="text-sm text-gray-600">Jawaban Salah</p>
                </div>
            </div>
        </div>

        <!-- Detail Jawaban -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Pembahasan Soal</h2>

            @foreach($detailJawaban as $index => $detail)
                @php
                    $soal = $soalList[$detail['soal_id']] ?? null;
                @endphp

                @if($soal)
                    <div class="mb-8 p-6 rounded-lg border {{ $detail['nilai'] > 0 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                        <!-- Header Soal -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded 
                                        @if($soal->jenis_soal == 'pilihan_ganda') bg-blue-100 text-blue-800
                                        @elseif($soal->jenis_soal == 'benar_salah') bg-purple-100 text-purple-800
                                        @else bg-orange-100 text-orange-800
                                        @endif">
                                        @if($soal->jenis_soal == 'pilihan_ganda')
                                            Pilihan Ganda
                                        @elseif($soal->jenis_soal == 'benar_salah')
                                            Benar Salah
                                        @else
                                            Pilihan Ganda Kompleks
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <span class="text-lg font-bold {{ $detail['nilai'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $detail['nilai'] }}/{{ $detail['maksimal'] }} poin
                            </span>
                        </div>

                        <!-- Pertanyaan -->
                        <div class="mb-4">
                            <p class="text-gray-800 font-medium mb-2">{!! nl2br(e($soal->pertanyaan)) !!}</p>
                            @if($soal->gambar_pertanyaan)
                                <img src="{{ Storage::url($soal->gambar_pertanyaan) }}" alt="Gambar Soal" class="max-w-md rounded-lg mt-2">
                            @endif
                        </div>

                        @if($soal->jenis_soal == 'pilihan_ganda')
                            <!-- Pilihan Jawaban -->
                            <div class="space-y-2 mb-4">
                                @foreach($soal->pilihanJawaban as $pilihan)
                                    <div class="p-3 rounded-lg border
                                        @if($pilihan->label == $detail['jawaban_benar'])
                                            border-green-500 bg-green-100
                                        @elseif($pilihan->label == $detail['jawaban_user'] && $detail['jawaban_user'] != $detail['jawaban_benar'])
                                            border-red-500 bg-red-100
                                        @else
                                            border-gray-300 bg-white
                                        @endif">
                                        <div class="flex items-center space-x-3">
                                            <span class="font-bold">{{ $pilihan->label }}.</span>
                                            <span>{{ $pilihan->teks_pilihan }}</span>
                                            @if($pilihan->label == $detail['jawaban_benar'])
                                                <span class="ml-auto text-green-600 font-semibold">✓ Jawaban Benar</span>
                                            @elseif($pilihan->label == $detail['jawaban_user'] && $detail['jawaban_user'] != $detail['jawaban_benar'])
                                                <span class="ml-auto text-red-600 font-semibold">✗ Jawaban Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        @elseif(in_array($soal->jenis_soal, ['benar_salah', 'mcma']))
                            <!-- Sub Soal -->
                            <div class="space-y-3 mb-4">
                                @foreach($soal->subSoal as $subSoal)
                                    @php
                                        $subDetail = collect($detail['detail'])->firstWhere('sub_soal_id', $subSoal->id);
                                    @endphp
                                    
                                    <div class="p-3 rounded-lg border {{ $subDetail['benar'] ? 'border-green-500 bg-green-100' : 'border-red-500 bg-red-100' }}">
                                        <div class="flex items-start justify-between mb-2">
                                            <p class="font-medium flex-1">{{ $subSoal->pernyataan }}</p>
                                            <span class="ml-4 {{ $subDetail['benar'] ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                                {{ $subDetail['benar'] ? '✓' : '✗' }}
                                            </span>
                                        </div>
                                        
                                        @if($soal->jenis_soal == 'benar_salah')
                                            <div class="text-sm">
                                                <span class="text-gray-700">Jawaban Anda: 
                                                    <span class="font-semibold">{{ $subDetail['jawaban_user'] == 'B' ? 'Benar' : ($subDetail['jawaban_user'] == 'S' ? 'Salah' : 'Tidak dijawab') }}</span>
                                                </span>
                                                @if(!$subDetail['benar'])
                                                    <span class="mx-2">|</span>
                                                    <span class="text-green-700">Jawaban yang benar: 
                                                        <span class="font-semibold">{{ $subDetail['jawaban_benar'] == 'B' ? 'Benar' : 'Salah' }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <!-- MCMA -->
                                            <div class="mt-2 space-y-1">
                                                @foreach($subSoal->subPilihanJawaban as $subPilihan)
                                                    <div class="text-sm flex items-center space-x-2
                                                        @if($subPilihan->label == $subDetail['jawaban_benar']) text-green-700 font-semibold @endif">
                                                        <span>{{ $subPilihan->label }}. {{ $subPilihan->teks_pilihan }}</span>
                                                        @if($subPilihan->label == $subDetail['jawaban_benar'])
                                                            <span>✓</span>
                                                        @endif
                                                        @if($subPilihan->label == $subDetail['jawaban_user'] && $subDetail['jawaban_user'] != $subDetail['jawaban_benar'])
                                                            <span class="text-red-600">← Jawaban Anda</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Pembahasan -->
                        @if($soal->pembahasan)
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="font-semibold text-blue-800 mb-2">📝 Pembahasan:</h4>
                                <p class="text-gray-700">{!! nl2br(e($soal->pembahasan)) !!}</p>
                                @if($soal->gambar_pembahasan)
                                    <img src="{{ Storage::url($soal->gambar_pembahasan) }}" alt="Gambar Pembahasan" class="max-w-md rounded-lg mt-2">
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach

            <!-- Action Buttons -->
            <div class="flex gap-4 mt-6">
                <a href="{{ route('simulasi.riwayat.nilai') }}" 
                   class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg text-center transition">
                    Lihat Riwayat Nilai
                </a>
                <a href="{{ route('simulasi.student.dashboard') }}" 
                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg text-center transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
