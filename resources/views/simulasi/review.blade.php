<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reviu Hasil Simulasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #e0f2fe 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header-card h1 {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .header-card .mata-pelajaran {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin-top: 12px;
        }

        .score-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .score-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            position: relative;
        }

        .score-circle::before {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: white;
            border-radius: 50%;
        }

        .score-text {
            position: relative;
            z-index: 1;
        }

        .score-text .nilai {
            font-size: 56px;
            font-weight: 800;
            color: #667eea;
            line-height: 1;
        }

        .score-text .label {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .stat-box {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            background: #f8fafc;
        }

        .stat-box .number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-box.benar .number {
            color: #22c55e;
        }

        .stat-box.salah .number {
            color: #ef4444;
        }

        .stat-box.total .number {
            color: #667eea;
        }

        .stat-box .label {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }

        .review-list {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .review-list h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 24px;
        }

        .review-item {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            border-left: 6px solid #e2e8f0; /* Default neutral */
        }

        .review-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .review-item.correct {
            border-left-color: #22c55e;
            background: #ffffff; /* Keep white for modern clean look */
        }

        .review-item.incorrect {
            border-left-color: #ef4444;
            background: #ffffff;
        }

        .review-header {
            display: flex;
            justify-content: space-between; /* Spread Number and Status */
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .review-number {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            background: #eff6ff;
            color: #3b82f6;
        }

        .review-item.correct .review-number {
            background: #dcfce7;
            color: #166534;
        }

        .review-item.incorrect .review-number {
            background: #fee2e2;
            color: #991b1b;
        }

        .review-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .review-item.correct .review-status {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
        }

        .review-item.incorrect .review-status {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .question-text {
            font-size: 16px;
            color: #1e293b;
            line-height: 1.6;
            margin-bottom: 16px;
            font-weight: 500;
        }

        .answer-info {
            display: grid;
            gap: 12px;
        }

        .answer-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            background: white;
        }

        .answer-label {
            font-weight: 600;
            color: #64748b;
            min-width: 120px;
        }

        .answer-value {
            font-weight: 600;
            color: #1e293b;
        }

        .answer-value.correct {
            color: #22c55e;
        }

        .answer-value.incorrect {
            color: #ef4444;
        }

        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .btn-selesai {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 48px;
            border-radius: 12px;
            border: none;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-selesai:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .score-circle {
                width: 150px;
                height: 150px;
            }

            .score-circle::before {
                width: 130px;
                height: 130px;
            }

            .score-text .nilai {
                font-size: 42px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header-card">
            <h1>Reviu Hasil Simulasi</h1>
            <div class="mata-pelajaran">
                <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle;">school</span>
                {{ $reviewData['simulasi']['mata_pelajaran'] }} - {{ $reviewData['simulasi']['nama'] }}
            </div>
        </div>

        <!-- Score Card (Modern Gradient Aesthetic) -->
        <div class="score-card" style="
            position: relative; 
            overflow: hidden; 
            padding: 50px; 
            border-radius: 24px; 
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            color: white;
        ">
            <!-- Decorative Overlay -->
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <div style="display: flex; align-items: center; justify-content: center; gap: 60px; position: relative; z-index: 2;">
                
                <!-- Col 1: Skor (Left) -->
                <div style="
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 24px;
                    padding: 30px;
                    text-align: center;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                    min-width: 200px;
                    transition: transform 0.3s ease;
                " onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="
                        width: 50px; height: 50px; 
                        background: #ecfdf5; border-radius: 50%; 
                        display: flex; align-items: center; justify-content: center; 
                        margin: 0 auto 15px;
                        color: #10b981;
                    ">
                        <span class="material-symbols-outlined" style="font-size: 28px;">checklist</span>
                    </div>
                    <div class="counter-anim" data-target="{{ $hasil['jumlah_benar'] }}" style="font-size: 64px; font-weight: 800; color: #1e293b; line-height: 1;">0</div>
                    <div style="font-size: 14px; font-weight: 600; color: #64748b; letter-spacing: 1px; margin-top: 5px;">SKOR POIN</div>
                </div>

                <!-- Col 2: Nilai (Right) -->
                <div style="
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 24px;
                    padding: 30px;
                    text-align: center;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                    min-width: 200px;
                    transition: transform 0.3s ease;
                " onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="
                        width: 50px; height: 50px; 
                        background: #eef2ff; border-radius: 50%; 
                        display: flex; align-items: center; justify-content: center; 
                        margin: 0 auto 15px;
                        color: #6366f1;
                    ">
                        <span class="material-symbols-outlined" style="font-size: 28px;">grade</span>
                    </div>
                    <div class="counter-anim" data-target="{{ round($nilai) }}" style="font-size: 64px; font-weight: 800; color: #1e293b; line-height: 1;">0</div>
                    <div style="font-size: 14px; font-weight: 600; color: #64748b; letter-spacing: 1px; margin-top: 5px;">NILAI</div>
                </div>

            </div>
        </div>

        <!-- Review List -->
        <div class="review-list">
            <h2>Detail Jawaban</h2>

            @php
                // Map detail jawaban agar mudah diakses
                $detailMap = [];
                foreach ($hasil['detail_jawaban'] as $dt) {
                   if (isset($dt['sub_soal_id'])) {
                       $detailMap['sub_' . $dt['sub_soal_id']] = $dt;
                   } else {
                       $detailMap['soal_' . $dt['soal_id']] = $dt;
                   }
                }
                
                $nomorSoal = 1;
            @endphp

            @foreach($soals as $soal)
                @php
                    // Helper to determine if we are in Soal or SubSoal iteration context
                    $loopItems = ($soal->subSoal && $soal->subSoal->count() > 0) ? $soal->subSoal : [$soal];
                @endphp

                @foreach($loopItems as $item)
                    @php
                        // Determine ID based on whether it is Soal or SubSoal
                        $itemId = ($item instanceof \App\Models\SubSoal) ? 'sub_' . $item->id : 'soal_' . $item->id;
                        $detail = $detailMap[$itemId] ?? null;

                        // Skip if no details (should not happen if logic matches)
                        if (!$detail) continue;

                        $isCorrect = ($detail['nilai'] >= 1 || (isset($detail['is_correct']) && $detail['is_correct']));
                        $jawabanUser = $detail['jawaban_user'] ?? null;
                        $kunciJawaban = $detail['jawaban_benar'];
                        
                        // Check if we have detailed break-down (from PenilaianService for Complex items)
                        $subDetails = $detail['detail'] ?? null;

                            // Calculate Score for Valid Display
                            $scoreObtained = $detail['nilai'] ?? 0;
                            $scoreMax = $detail['maksimal'] ?? 1;
                        @endphp

                        <div class="review-item {{ $isCorrect ? 'correct' : 'incorrect' }}">
                            <div class="review-header">
                                <div class="review-number">{{ $nomorSoal++ }}</div>
                                <div class="review-status" style="background: #334155; color: white;">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">grade</span>
                                    Skor: {{ $scoreObtained }} / {{ $scoreMax }}
                                </div>
                            </div>

                        <div class="question-text">
                            @if($item instanceof \App\Models\SubSoal) 
                                <strong>{{ $soal->pertanyaan }}</strong>
                                @if($soal->gambar)
                                    <br><img src="{{ asset('storage/' . $soal->gambar) }}" style="max-width: 100%; margin: 10px 0; border-radius: 8px;" alt="Gambar Soal">
                                @endif
                                <br> - 
                            @endif
                            {!! nl2br(e($item->pertanyaan)) !!}
                            @if($item->gambar)
                                <br><img src="{{ asset('storage/' . $item->gambar) }}" style="max-width: 100%; margin: 10px 0; border-radius: 8px;" alt="Gambar Sub Soal">
                            @endif
                        </div>

                        {{-- DISPLAY LOGIC START --}}
                        <div class="answer-info">
                            
                            {{-- CASE 1: COMPLEX / TABLE VIEW (e.g. Benar/Salah Complex) --}}
                            @if(!empty($subDetails) && is_array($subDetails))
                                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                                    <thead>
                                        <tr style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                                            <th style="padding: 10px; text-align: left;">Pernyataan</th>
                                            <th style="padding: 10px; text-align: center;">Jawaban Anda</th>
                                            <th style="padding: 10px; text-align: center;">Kunci</th>
                                            <th style="padding: 10px; text-align: center;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subDetails as $sd)
                                            <tr style="border-bottom: 1px solid #eee;">
                                                <td style="padding: 10px;">{{ $sd['teks'] ?? '-' }}</td>
                                                <td style="padding: 10px; text-align: center; font-weight: bold;">
                                                    <span style="{{ $sd['is_correct'] ? 'color: #22c55e' : 'color: #ef4444' }}">
                                                        {{ $sd['user'] ?? '-' }}
                                                    </span>
                                                </td>
                                                <td style="padding: 10px; text-align: center;">{{ $sd['kunci'] }}</td>
                                                <td style="padding: 10px; text-align: center;">
                                                    @if($sd['is_correct'])
                                                        <span class="material-symbols-outlined" style="color: #22c55e;">check_circle</span>
                                                    @else
                                                        <span class="material-symbols-outlined" style="color: #ef4444;">cancel</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            {{-- CASE 2: STANDARD OPTIONS VIEW (Pilihan Ganda / MCMA with Options) --}}
                            @elseif($item->pilihanJawaban && $item->pilihanJawaban->count() > 0)
                                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                                    @php
                                        // Normalize user answer to array for checking
                                        $userAnsArr = is_array($jawabanUser) ? $jawabanUser : explode(',', $jawabanUser ?? '');
                                        $userAnsArr = array_map('trim', $userAnsArr);
                                        
                                        $keyAnsArr = explode(',', $kunciJawaban ?? '');
                                        $keyAnsArr = array_map('trim', $keyAnsArr);
                                    @endphp

                                    @foreach($item->pilihanJawaban as $idx => $opt)
                                        @php
                                            $label = chr(65 + $idx); // A, B, C...
                                            // Check using Label/Value matching specific to question type implementation
                                            // Assuming answer is stored as 'A', 'B' OR Option text? 
                                            // Standard is 'A', 'B' usually.
                                            
                                            $isSelected = in_array($label, $userAnsArr);
                                            $isKey = in_array($label, $keyAnsArr); // Or check $opt->is_benar?
                                            // Fallback: Check if Key matches $opt->id? No usually strings.
                                            // If key is mapped to 'A', then we match 'A'.
                                            
                                            // Correctness logic for this specific option
                                            $optCorrect = ($isSelected && $isKey); 
                                            $optWrong = ($isSelected && !$isKey);
                                            $optMissed = (!$isSelected && $isKey);
                                        @endphp
                                        
                                        <div style="
                                            display: flex; align-items: flex-start; gap: 10px; padding: 10px; border-radius: 8px;
                                            border: 1px solid {{ $isSelected ? ($isKey ? '#22c55e' : '#ef4444') : '#e2e8f0' }};
                                            background: {{ $isSelected ? ($isKey ? '#f0fdf4' : '#fef2f2') : 'white' }};
                                        ">
                                            <div style="
                                                min-width: 24px; height: 24px; border-radius: 50%; 
                                                border: 2px solid {{ $isSelected ? 'currentColor' : '#cbd5e1'}};
                                                display: flex; align-items: center; justify-content: center; font-weight: bold;
                                                color: {{ $isSelected ? ($isKey ? '#22c55e' : '#ef4444') : '#64748b' }};
                                            ">
                                                {{ $label }}
                                            </div>
                                            
                                            <div style="flex: 1;">
                                                <div>{{ $opt->teks_jawaban }}</div>
                                                @if($opt->gambar_jawaban)
                                                    <img src="/storage/{{ $opt->gambar_jawaban }}" style="max-height: 80px; margin-top: 5px; border-radius: 4px;">
                                                @endif
                                            </div>

                                            <div style="min-width: 30px; text-align: right;">
                                                @if($isSelected)
                                                    @if($isKey)
                                                        <span class="material-symbols-outlined" style="color: #22c55e;" title="Jawaban Benar">check_circle</span>
                                                    @else
                                                        <span class="material-symbols-outlined" style="color: #ef4444;" title="Jawaban Salah">cancel</span>
                                                    @endif
                                                @elseif($isKey)
                                                     <span class="material-symbols-outlined" style="color: #22c55e; opacity: 0.5;" title="Kunci Jawaban">check</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            {{-- CASE 3: TEXT VIEW (Fallback for no options / Isian / simple) --}}
                            @else
                                <div class="answer-row">
                                    <div class="answer-label">Jawaban Anda:</div>
                                    <div class="answer-value {{ $isCorrect ? 'correct' : 'incorrect' }}">
                                        {{ $jawabanUser ?? '-' }}
                                        @if($isCorrect)
                                            <span class="material-symbols-outlined" style="vertical-align: middle; margin-left: 5px;">check_circle</span>
                                        @else
                                            <span class="material-symbols-outlined" style="vertical-align: middle; margin-left: 5px;">cancel</span>
                                        @endif
                                    </div>
                                </div>
                                @if(!$isCorrect)
                                    <div class="answer-row">
                                        <div class="answer-label">Kunci Jawaban:</div>
                                        <div class="answer-value correct">
                                            {{ $kunciJawaban }}
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        <!-- Action Button -->
        <div class="action-buttons">
            <button onclick="window.location.href='{{ route('simulasi.student.logout') }}'" class="btn-selesai" style="border: none; cursor: pointer;">
                <span class="material-symbols-outlined">logout</span>
                Kembali ke Halaman Login
            </button>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const counters = document.querySelectorAll('.counter-anim');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const duration = 2000; // 2 seconds
                const frameDuration = 1000 / 60; 
                const totalFrames = Math.round(duration / frameDuration);
                const easeOutQuad = t => t * (2 - t);
                
                let frame = 0;
                const updateCount = () => {
                    frame++;
                    const progress = easeOutQuad(frame / totalFrames);
                    const current = Math.round(target * progress);

                    if (frame < totalFrames) {
                        counter.innerText = current;
                        requestAnimationFrame(updateCount);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCount();
            });
        });
    </script>
</body>
</html>
