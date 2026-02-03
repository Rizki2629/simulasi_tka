<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Hasil {{ $reviewData['simulasi']['nama'] ?? 'Simulasi' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #8B1538;
        }
        
        .header h1 {
            font-size: 18px;
            color: #8B1538;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14px;
            color: #666;
            font-weight: normal;
            margin-bottom: 3px;
        }
        
        .student-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #8B1538;
        }
        
        .student-info table {
            width: 100%;
        }
        
        .student-info td {
            padding: 3px 0;
        }
        
        .student-info td:first-child {
            width: 150px;
            font-weight: bold;
            color: #555;
        }
        
        .score-summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .score-box {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .score-box:first-child {
            border-radius: 5px 0 0 5px;
        }
        
        .score-box:last-child {
            border-radius: 0 5px 5px 0;
        }
        
        .score-box .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .score-box .value {
            font-size: 20px;
            font-weight: bold;
            color: #8B1538;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #8B1538;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #8B1538;
        }
        
        .question-block {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .question-header {
            background: #f1f3f5;
            padding: 8px 10px;
            border-left: 4px solid #8B1538;
            margin-bottom: 8px;
        }
        
        .question-text {
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .question-image {
            max-width: 400px;
            height: auto;
            margin: 10px 0;
            display: block;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        
        .option-image {
            max-width: 200px;
            height: auto;
            margin-top: 5px;
            display: block;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        
        .options {
            margin: 10px 0;
        }
        
        .option {
            padding: 6px 10px;
            margin: 5px 0;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background: #fff;
        }
        
        .option.correct {
            background: #d4edda;
            border-color: #28a745;
            font-weight: bold;
        }
        
        .option.wrong {
            background: #f8d7da;
            border-color: #dc3545;
        }
        
        .option.user-answer {
            border: 2px solid #007bff;
            background: #e7f3ff;
        }
        
        .answer-info {
            margin-top: 10px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 10px;
        }
        
        .answer-info strong {
            color: #8B1538;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-correct {
            background: #28a745;
            color: white;
        }
        
        .badge-wrong {
            background: #dc3545;
            color: white;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        table {
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REVIEW HASIL SIMULASI</h1>
        <h2>{{ $reviewData['simulasi']['nama'] ?? 'Simulasi' }}</h2>
        <h2>{{ $reviewData['simulasi']['mata_pelajaran'] ?? '-' }}</h2>
    </div>
    
    <div class="student-info">
        <table>
            <tr>
                <td>Nama Lengkap</td>
                <td>: {{ $student->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>: {{ $student->nisn ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>: {{ $student->rombongan_belajar ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Ujian</td>
                <td>: {{ $nilai->created_at->format('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>
    
    <div class="score-summary">
        <div class="score-box">
            <div class="label">Total Skor Poin</div>
            <div class="value">{{ $skorPoin }}</div>
            <div style="font-size: 9px; color: #999;">Skor maksimal: {{ $skorMaksimal }}</div>
        </div>
        <div class="score-box">
            <div class="label">Nilai Akhir</div>
            <div class="value">{{ number_format($hasil['nilai_total'] ?? 0, 1) }}</div>
            <div style="font-size: 9px; color: #999;">Skala 0-100</div>
        </div>
        <div class="score-box">
            <div class="label">Jumlah Benar</div>
            <div class="value" style="color: #28a745;">{{ $hasil['jumlah_benar'] ?? 0 }}</div>
            <div style="font-size: 9px; color: #999;">dari {{ $hasil['jumlah_soal'] ?? 0 }} soal</div>
        </div>
        <div class="score-box">
            <div class="label">Jumlah Salah</div>
            <div class="value" style="color: #dc3545;">{{ $hasil['jumlah_salah'] ?? 0 }}</div>
            <div style="font-size: 9px; color: #999;">dari {{ $hasil['jumlah_soal'] ?? 0 }} soal</div>
        </div>
    </div>
    
    <div class="section-title">Detail Jawaban</div>
    
    @php
        $nomorSoal = 1;
        $detailJawaban = $hasil['detail_jawaban'] ?? [];
        $detailBySubSoal = collect($detailJawaban)->keyBy('sub_soal_id');
    @endphp
    
    @foreach($soals as $soal)
        @if($soal->subSoal && $soal->subSoal->isNotEmpty())
            {{-- Tampilkan pertanyaan paket jika ada --}}
            @if($soal->pertanyaan)
                <div style="background: #f8f9fa; padding: 12px; border-left: 4px solid #6c757d; margin-bottom: 15px; border-radius: 4px;">
                    <strong style="color: #495057;">Soal Paket:</strong>
                    <div style="margin-top: 5px;">{!! $soal->pertanyaan !!}</div>
                    @if($soal->gambar_pertanyaan)
                        <img src="{{ public_path('storage/' . $soal->gambar_pertanyaan) }}" class="question-image" alt="Gambar Soal Paket" style="margin-top: 10px;">
                    @endif
                </div>
            @endif
            
            @foreach($soal->subSoal as $subSoal)
                @php
                    $detail = $detailBySubSoal->get($subSoal->id);
                    $isCorrect = $detail && ($detail['nilai'] ?? 0) > 0;
                @endphp
                
                <div class="question-block">
                    <div class="question-header">
                        <strong>Soal {{ $nomorSoal }}</strong>
                        <span class="badge {{ $isCorrect ? 'badge-correct' : 'badge-wrong' }}" style="float: right;">
                            {{ $isCorrect ? '✓ Benar' : '✗ Salah' }}
                        </span>
                    </div>
                    
                    <div class="question-text">
                        {!! $subSoal->pertanyaan ?? '-' !!}
                    </div>
                    
                    @if($subSoal->gambar_pertanyaan)
                        <img src="{{ public_path('storage/' . $subSoal->gambar_pertanyaan) }}" class="question-image" alt="Gambar Soal">
                    @endif
                    
                    @if($subSoal->jenis_soal === 'pilihan_ganda' || $subSoal->jenis_soal === 'multiple_choice_multiple_answer')
                        <div class="options">
                            @foreach($subSoal->pilihanJawaban as $pilihan)
                                @php
                                    $jawabanUser = $detail['jawaban_user'] ?? '';
                                    $jawabanBenar = $detail['jawaban_benar'] ?? '';
                                    
                                    if (is_array($jawabanUser)) {
                                        $isUserAnswer = in_array($pilihan->label, $jawabanUser);
                                    } else {
                                        $jawabArray = array_filter(array_map('trim', explode(',', $jawabanUser)));
                                        $isUserAnswer = in_array($pilihan->label, $jawabArray);
                                    }
                                    
                                    if (is_array($jawabanBenar)) {
                                        $isCorrectAnswer = in_array($pilihan->label, $jawabanBenar);
                                    } else {
                                        $benarArray = array_filter(array_map('trim', explode(',', $jawabanBenar)));
                                        $isCorrectAnswer = in_array($pilihan->label, $benarArray);
                                    }
                                    
                                    $optionClass = '';
                                    if ($isCorrectAnswer) {
                                        $optionClass = 'correct';
                                    } elseif ($isUserAnswer) {
                                        $optionClass = 'wrong';
                                    }
                                @endphp
                                
                                <div class="option {{ $optionClass }}">
                                    <strong>{{ $pilihan->label }}.</strong> {!! $pilihan->isi_pilihan !!}
                                    @if($pilihan->gambar)
                                        <img src="{{ public_path('storage/' . $pilihan->gambar) }}" class="option-image" alt="Gambar Pilihan">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @elseif($subSoal->jenis_soal === 'benar_salah')
                        <div class="answer-info">
                            <strong>Jawaban Anda:</strong> 
                            @if(is_array($detail['jawaban_user'] ?? null))
                                {{ json_encode($detail['jawaban_user']) }}
                            @else
                                {{ $detail['jawaban_user'] ?? '-' }}
                            @endif
                            <br>
                            <strong>Jawaban Benar:</strong> 
                            @if(is_array($detail['jawaban_benar'] ?? null))
                                {{ json_encode($detail['jawaban_benar']) }}
                            @else
                                {{ $detail['jawaban_benar'] ?? '-' }}
                            @endif
                        </div>
                    @endif
                    
                    <div class="answer-info">
                        <strong>Poin:</strong> {{ $detail['nilai'] ?? 0 }} / {{ $detail['maksimal'] ?? 1 }}
                    </div>
                </div>
                
                @php $nomorSoal++; @endphp
            @endforeach
        @else
            @php
                $detail = null;
                foreach($detailJawaban as $d) {
                    if (($d['soal_id'] ?? 0) == $soal->id && empty($d['sub_soal_id'])) {
                        $detail = $d;
                        break;
                    }
                }
                $isCorrect = $detail && ($detail['nilai'] ?? 0) > 0;
            @endphp
            
            <div class="question-block">
                <div class="question-header">
                    <strong>Soal {{ $nomorSoal }}</strong>
                    <span class="badge {{ $isCorrect ? 'badge-correct' : 'badge-wrong' }}" style="float: right;">
                        {{ $isCorrect ? '✓ Benar' : '✗ Salah' }}
                    </span>
                </div>
                
                <div class="question-text">
                    {!! $soal->pertanyaan ?? '-' !!}
                </div>
                
                @if($soal->gambar_pertanyaan)
                    <img src="{{ public_path('storage/' . $soal->gambar_pertanyaan) }}" class="question-image" alt="Gambar Soal">
                @endif
                
                @if($soal->jenis_soal === 'pilihan_ganda' || $soal->jenis_soal === 'multiple_choice_multiple_answer')
                    <div class="options">
                        @foreach($soal->pilihanJawaban as $pilihan)
                            @php
                                $jawabanUser = $detail['jawaban_user'] ?? '';
                                $jawabanBenar = $detail['jawaban_benar'] ?? '';
                                
                                if (is_array($jawabanUser)) {
                                    $isUserAnswer = in_array($pilihan->label, $jawabanUser);
                                } else {
                                    $jawabArray = array_filter(array_map('trim', explode(',', $jawabanUser)));
                                    $isUserAnswer = in_array($pilihan->label, $jawabArray);
                                }
                                
                                if (is_array($jawabanBenar)) {
                                    $isCorrectAnswer = in_array($pilihan->label, $jawabanBenar);
                                } else {
                                    $benarArray = array_filter(array_map('trim', explode(',', $jawabanBenar)));
                                    $isCorrectAnswer = in_array($pilihan->label, $benarArray);
                                }
                                
                                $optionClass = '';
                                if ($isCorrectAnswer) {
                                    $optionClass = 'correct';
                                } elseif ($isUserAnswer) {
                                    $optionClass = 'wrong';
                                }
                            @endphp
                            
                            <div class="option {{ $optionClass }}">
                                <strong>{{ $pilihan->label }}.</strong> {!! $pilihan->isi_pilihan !!}
                                @if($pilihan->gambar)
                                    <img src="{{ public_path('storage/' . $pilihan->gambar) }}" class="option-image" alt="Gambar Pilihan">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif($soal->jenis_soal === 'benar_salah')
                    <div class="answer-info">
                        <strong>Jawaban Anda:</strong> {{ $detail['jawaban_user'] ?? '-' }}<br>
                        <strong>Jawaban Benar:</strong> {{ $detail['jawaban_benar'] ?? '-' }}
                    </div>
                @endif
                
                <div class="answer-info">
                    <strong>Poin:</strong> {{ $detail['nilai'] ?? 0 }} / {{ $detail['maksimal'] ?? 1 }}
                </div>
            </div>
            
            @php $nomorSoal++; @endphp
        @endif
    @endforeach
    
    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh Sistem Simulasi TKA</p>
        <p>Dicetak pada: {{ now()->format('d F Y, H:i:s') }} WIB</p>
    </div>
</body>
</html>
