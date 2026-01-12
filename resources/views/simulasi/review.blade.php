<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Review Hasil Simulasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="p-4 md:p-8 text-slate-800">
@php
    $simulasiNama = data_get($reviewData, 'simulasi.nama', '-');
    $mapelNama = data_get($reviewData, 'simulasi.mata_pelajaran', '-');
    $namaSiswa = $student->name ?? session('student_name', '-');
    $kelasSiswa = $student->rombongan_belajar ?? '-';

    $detailJawaban = is_array($hasil ?? null) ? ($hasil['detail_jawaban'] ?? []) : [];
    if (!is_array($detailJawaban)) {
        $detailJawaban = [];
    }

    $jenisLabelMap = [
        'pilihan_ganda' => 'Pilihan Ganda',
        'benar_salah' => 'Benar / Salah',
        'mcma' => 'MCMA',
        'isian' => 'Isian',
        'uraian' => 'Uraian',
    ];

    // Map detail jawaban agar mudah diakses
    $detailMap = [];
    foreach ($detailJawaban as $dt) {
        if (!is_array($dt)) continue;
        if (isset($dt['sub_soal_id'])) {
            $detailMap['sub_' . $dt['sub_soal_id']] = $dt;
        } elseif (isset($dt['soal_id'])) {
            $detailMap['soal_' . $dt['soal_id']] = $dt;
        }
    }

    // Count how many items will be rendered (soal-level vs sub-soal level)
    $jumlahDitampilkan = 0;
    foreach ($soals as $soal) {
        $parentKey = 'soal_' . $soal->id;
        if (isset($detailMap[$parentKey])) {
            $jumlahDitampilkan += 1;
            continue;
        }

        if ($soal->subSoal && $soal->subSoal->count() > 0) {
            foreach ($soal->subSoal as $sub) {
                if (isset($detailMap['sub_' . $sub->id])) {
                    $jumlahDitampilkan += 1;
                }
            }
        } else {
            if (isset($detailMap['soal_' . $soal->id])) {
                $jumlahDitampilkan += 1;
            }
        }
    }
@endphp

    <div class="max-w-6xl mx-auto">
        <div class="bg-gradient-to-b from-cyan-100 via-cyan-50 to-white rounded-3xl shadow-md mb-6 border border-slate-100 overflow-hidden">
            <div class="relative flex items-center justify-between px-6 py-8 md:px-12 md:py-10">
                <img
                    src="{{ asset('images/imgi_24_tl.png') }}"
                    alt="Ilustrasi"
                    class="hidden md:block h-28 md:h-36 lg:h-40 w-auto select-none"
                    draggable="false"
                />

                <div class="flex-1 text-center px-4 relative">
                    <img
                        src="{{ asset('images/imgi_82_width_90.webp') }}"
                        alt="Logo"
                        class="pointer-events-none select-none absolute left-1/2 top-[-10%] -translate-x-1/2 -translate-y-1/2 w-[250px] h-[200px] object-contain opacity-100 z-0 drop-shadow-sm"
                        draggable="false"
                    />
                    <div class="relative z-10 mt-8 text-3xl md:text-5xl font-extrabold tracking-wide text-orange-500">
                        REVIEW HASIL SIMULASI
                    </div>
                    <div class="relative z-10 mt-1 text-2xl md:text-4xl font-extrabold text-slate-900">
                        {{ strtoupper($simulasiNama) }}
                    </div>
                </div>

                <img
                    src="{{ asset('images/imgi_83_t.png') }}"
                    alt="Ilustrasi"
                    class="hidden md:block h-28 md:h-36 lg:h-40 w-auto select-none"
                    draggable="false"
                />
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="fas fa-user text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Nama Lengkap</p>
                        <p class="text-lg font-bold text-slate-800">{{ $namaSiswa }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4 md:border-l md:border-slate-100 md:pl-6">
                    <div class="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center text-orange-600">
                        <i class="fas fa-graduation-cap text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Kelas</p>
                        <p class="text-lg font-bold text-slate-800">{{ $kelasSiswa }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-blue-500 flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-medium">TOTAL SKOR POIN</p>
                    <p class="text-3xl font-bold text-slate-800">{{ (int) round($skorPoin ?? ($hasil['jumlah_benar'] ?? 0)) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Skor maksimal: {{ (int) ($skorMaksimal ?? ($hasil['jumlah_soal'] ?? 0)) }}</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-xl">
                    <i class="fas fa-check-double text-blue-500 text-xl"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-purple-500 flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-medium">NILAI AKHIR</p>
                    <p class="text-3xl font-bold text-slate-800">{{ (int) round($nilai ?? 0) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Rumus: round((skor/skor maksimal) * 100)</p>
                </div>
                <div class="bg-purple-50 p-3 rounded-xl">
                    <i class="fas fa-star text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h2 class="text-lg font-bold text-slate-700">Detail Jawaban</h2>
                <span class="text-sm font-medium text-slate-500 italic">Menampilkan {{ $jumlahDitampilkan }} Soal</span>
            </div>

            <div class="p-6">
                @php $nomorSoal = 1; @endphp

                @foreach($soals as $soal)
                    @php
                        $parentKey = 'soal_' . $soal->id;
                        $hasParentDetail = isset($detailMap[$parentKey]);

                        $loopItems = $hasParentDetail
                            ? [$soal]
                            : (($soal->subSoal && $soal->subSoal->count() > 0) ? $soal->subSoal : [$soal]);
                    @endphp

                    @foreach($loopItems as $item)
                        @php
                            $itemId = ($item instanceof \App\Models\SubSoal) ? 'sub_' . $item->id : 'soal_' . $item->id;
                            $detail = $detailMap[$itemId] ?? null;
                            if (!$detail) continue;

                            $jawabanUser = $detail['jawaban_user'] ?? null;
                            $kunciJawaban = $detail['jawaban_benar'] ?? '-';
                            $subDetails = $detail['detail'] ?? null;
                            $scoreObtained = $detail['nilai'] ?? 0;
                            $scoreMax = $detail['maksimal'] ?? 1;

                            $jenis = $soal->jenis_soal ?? ($item->jenis_soal ?? null);
                            $jenisLabel = $jenisLabelMap[$jenis] ?? (is_string($jenis) ? $jenis : 'Soal');

                            $soalGambar = $soal->gambar_pertanyaan ?? ($soal->gambar ?? null);
                            $itemGambar = $item->gambar_pertanyaan ?? ($item->gambar ?? null);
                        @endphp

                        <div class="border border-slate-200 rounded-xl overflow-hidden mb-6">
                            <div class="bg-slate-50 p-4 border-b border-slate-200 flex justify-between items-center gap-3">
                                <div class="min-w-0">
                                    <span class="bg-slate-800 text-white px-3 py-1 rounded-lg text-sm font-bold mr-3">{{ $nomorSoal++ }}</span>
                                    <span class="font-semibold text-slate-700 truncate">Soal {{ $jenisLabel }}</span>
                                </div>
                                <div class="bg-amber-100 text-amber-700 px-3 py-1 rounded-md text-xs font-bold whitespace-nowrap">
                                    SKOR: {{ $scoreObtained }} / {{ $scoreMax }}
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="text-slate-600 mb-4 leading-relaxed">
                                    @if($item instanceof \App\Models\SubSoal)
                                        <div class="font-semibold text-slate-700">{{ $soal->pertanyaan }}</div>
                                        @if($soalGambar)
                                            <img src="{{ asset('storage/' . $soalGambar) }}" class="mt-3 max-w-full rounded-xl border border-slate-200" alt="Gambar Soal">
                                        @endif
                                        <div class="mt-3">-</div>
                                    @endif
                                    {!! nl2br(e($item->pertanyaan)) !!}
                                    @if($itemGambar)
                                        <img src="{{ asset('storage/' . $itemGambar) }}" class="mt-3 max-w-full rounded-xl border border-slate-200" alt="Gambar Soal">
                                    @endif
                                </div>

                                {{-- CASE 1: COMPLEX / TABLE VIEW --}}
                                @if(!empty($subDetails) && is_array($subDetails))
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left border-collapse">
                                            <thead>
                                                <tr class="text-slate-400 text-xs uppercase tracking-wider">
                                                    <th class="py-3 px-4 font-semibold border-b">Pernyataan</th>
                                                    <th class="py-3 px-4 font-semibold border-b text-center">Jawaban Anda</th>
                                                    <th class="py-3 px-4 font-semibold border-b text-center">Kunci</th>
                                                    <th class="py-3 px-4 font-semibold border-b text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-sm">
                                                @foreach($subDetails as $sd)
                                                    @php
                                                        $subSoalId = $sd['sub_soal_id'] ?? null;
                                                        $subSoalText = '-';
                                                        if ($subSoalId && $soal->subSoal) {
                                                            $matched = $soal->subSoal->firstWhere('id', (int) $subSoalId);
                                                            if ($matched) {
                                                                $subSoalText = $matched->pertanyaan;
                                                            }
                                                        }

                                                        $sdIsCorrect = $sd['is_correct'] ?? ((int) ($sd['benar'] ?? 0) === 1);
                                                        $sdUser = $sd['user'] ?? ($sd['jawaban_user'] ?? '-');
                                                        $sdKunci = $sd['kunci'] ?? ($sd['jawaban_benar'] ?? '-');
                                                        $sdTeks = $sd['teks'] ?? $subSoalText;
                                                    @endphp

                                                    <tr class="{{ $sdIsCorrect ? 'bg-emerald-50/40' : 'bg-red-50/30' }}">
                                                        <td class="py-3 px-4 border-b text-slate-600">{{ $sdTeks }}</td>
                                                        <td class="py-3 px-4 border-b text-center font-bold {{ $sdIsCorrect ? 'text-emerald-600' : 'text-red-600' }} text-lg">{{ $sdUser }}</td>
                                                        <td class="py-3 px-4 border-b text-center font-bold text-slate-700 text-lg">{{ $sdKunci }}</td>
                                                        <td class="py-3 px-4 border-b text-center">
                                                            @if($sdIsCorrect)
                                                                <i class="fas fa-check-circle text-emerald-500"></i>
                                                            @else
                                                                <i class="fas fa-times-circle text-red-500"></i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                {{-- CASE 2: OPTIONS VIEW (PG/MCMA) --}}
                                @elseif($item->pilihanJawaban && $item->pilihanJawaban->count() > 0)
                                    @php
                                        $userAnsArr = is_array($jawabanUser) ? $jawabanUser : explode(',', $jawabanUser ?? '');
                                        $userAnsArr = array_values(array_filter(array_map('trim', $userAnsArr)));

                                        $keyAnsArr = is_array($kunciJawaban) ? $kunciJawaban : explode(',', $kunciJawaban ?? '');
                                        $keyAnsArr = array_values(array_filter(array_map('trim', $keyAnsArr)));
                                    @endphp

                                    <div class="space-y-2">
                                        @foreach($item->pilihanJawaban as $idx => $opt)
                                            @php
                                                $label = chr(65 + $idx);
                                                $isSelected = in_array($label, $userAnsArr);
                                                $isKey = in_array($label, $keyAnsArr);

                                                $border = !$isSelected ? 'border-slate-200' : ($isKey ? 'border-emerald-300' : 'border-red-300');
                                                $bg = !$isSelected ? 'bg-white' : ($isKey ? 'bg-emerald-50' : 'bg-red-50');
                                                $pill = !$isSelected ? 'text-slate-500 border-slate-300' : ($isKey ? 'text-emerald-700 border-emerald-400' : 'text-red-700 border-red-400');
                                            @endphp

                                            <div class="flex items-start gap-3 p-3 rounded-xl border {{ $border }} {{ $bg }}">
                                                <div class="w-7 h-7 rounded-full border flex items-center justify-center text-sm font-bold {{ $pill }}">
                                                    {{ $label }}
                                                </div>

                                                <div class="flex-1 text-slate-700">
                                                    <div>{{ $opt->teks_jawaban }}</div>
                                                    @if($opt->gambar_jawaban)
                                                        <img src="{{ asset('storage/' . $opt->gambar_jawaban) }}" class="mt-2 max-h-24 rounded-lg border border-slate-200" alt="Gambar Jawaban">
                                                    @endif
                                                </div>

                                                <div class="w-7 text-right pt-1">
                                                    @if($isSelected)
                                                        @if($isKey)
                                                            <i class="fas fa-check-circle text-emerald-500" title="Jawaban Benar"></i>
                                                        @else
                                                            <i class="fas fa-times-circle text-red-500" title="Jawaban Salah"></i>
                                                        @endif
                                                    @elseif($isKey)
                                                        <i class="fas fa-check text-emerald-500/60" title="Kunci Jawaban"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                {{-- CASE 3: TEXT VIEW --}}
                                @else
                                    @php
                                        $isCorrect = ($detail['nilai'] ?? 0) >= 1 || (isset($detail['is_correct']) && $detail['is_correct']);
                                    @endphp

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50">
                                            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Jawaban Anda</div>
                                            <div class="text-lg font-bold {{ $isCorrect ? 'text-emerald-700' : 'text-red-700' }}">
                                                {{ $jawabanUser ?? '-' }}
                                            </div>
                                        </div>

                                        <div class="p-4 rounded-xl border border-slate-200 bg-white">
                                            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Kunci Jawaban</div>
                                            <div class="text-lg font-bold text-slate-700">
                                                {{ $kunciJawaban }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach

                <div class="pt-2">
                    <form method="POST" action="{{ route('simulasi.student.logout') }}">
                        @csrf
                        <button type="submit" class="w-full md:w-auto inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800">
                            <i class="fas fa-right-from-bracket"></i>
                            Kembali ke Halaman Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
