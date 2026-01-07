<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Soal - Simulasi TKA</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles (Consistent with Index) */
        .sidebar {
            width: 280px;
            background: #702637;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        /* ... Reuse sidebar styles from index ... */
        .sidebar-header { padding: 24px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo-icon { width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #702637; font-weight: bold; font-size: 20px; }
        .logo-text { font-size: 16px; font-weight: 600; line-height: 1.2; }
        .sidebar-nav { padding: 20px 0; }
        .menu-item { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: all 0.2s ease; }
        .menu-item:hover { background: rgba(255, 255, 255, 0.08); color: white; }
        .menu-item.active { background: rgba(255, 255, 255, 0.12); color: white; }
        .menu-item-text { flex: 1; font-size: 14px; }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: white;
            padding: 16px 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .content {
            flex: 1;
            padding: 32px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 16px;
            color: #666;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            color: #333;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #f5f5f5;
            border-color: #ccc;
        }

        .btn-edit {
            background: #702637;
            color: white;
            border: none;
        }
        
        .btn-edit:hover {
            background: #5a1e2d;
        }

        /* Detail Card */
        .detail-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #eee;
            margin-bottom: 24px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .meta-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .meta-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .question-block {
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 1px dashed #eee;
        }

        .question-block:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .question-number {
            font-size: 18px;
            font-weight: 600;
            color: #702637;
            background: #fdf2f4;
            padding: 4px 12px;
            border-radius: 6px;
        }

        .question-type {
            font-size: 14px;
            color: #666;
            background: #f5f5f5;
            padding: 4px 12px;
            border-radius: 6px;
        }

        .question-text {
            font-size: 16px;
            line-height: 1.6;
            color: #1a1a1a;
            margin-bottom: 20px;
            white-space: pre-wrap;
        }

        .question-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 12px;
            border: 1px solid #eee;
            margin-bottom: 24px;
            display: block;
        }

        .answers-grid {
            display: grid;
            gap: 12px;
        }

        .answer-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            border: 1px solid #eee; /**/
            border-radius: 12px;
            background: #fff;
        }

        .answer-item.correct {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .answer-label {
            width: 32px;
            height: 32px;
            background: #f5f5f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #666;
            flex-shrink: 0;
        }

        .answer-item.correct .answer-label {
            background: #22c55e;
            color: white;
        }

        .answer-content {
            flex: 1;
        }

        .answer-text {
            font-size: 15px;
            color: #333;
            line-height: 1.5;
        }

        .answer-image {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 8px;
            border: 1px solid #eee;
        }

        .explanation {
            margin-top: 24px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #702637;
        }

        .explanation-title {
            font-size: 14px;
            font-weight: 600;
            color: #702637;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Code reused mostly from Index -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                     <span class="material-symbols-outlined" style="color: white; font-size: 32px;">school</span>
                     <div class="logo-text">SIMULASI TKA<br>SDN GU 09</div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="/soal" class="menu-item">
                    <span class="material-symbols-outlined">arrow_back</span>
                    <span class="menu-item-text">Kembali ke Daftar</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <div>
                    <div style="font-size: 12px; color: #999;">Detail Soal</div>
                    <div style="font-size: 16px; font-weight: 600;">{{ $soal->kode_soal }}</div>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="/soal" class="btn-secondary">Kembali</a>
                    <a href="/soal/{{ $soal->id }}/edit" class="btn-secondary btn-edit" style="color: white;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                        Edit Soal
                    </a>
                </div>
            </header>

            <div class="content">
                <div class="detail-card">
                    <div class="meta-grid">
                        <div class="meta-item">
                            <span class="meta-label">Mata Pelajaran</span>
                            <span class="meta-value">{{ $soal->mataPelajaran->nama ?? '-' }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Kode Soal</span>
                            <span class="meta-value">{{ $soal->kode_soal }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Tanggal Dibuat</span>
                            <span class="meta-value">{{ $soal->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Jenis</span>
                            <span class="meta-value">{{ ucfirst($soal->jenis_soal) }}</span>
                        </div>
                    </div>

                    @if($soal->subSoal->count() > 0)
                        <!-- Loop through SubSoal -->
                        @foreach($soal->subSoal as $index => $sub)
                            <div class="question-block">
                                <div class="question-header">
                                    <span class="question-number">Soal Nomor {{ $sub->nomor_urut }}</span>
                                    <span class="question-type">{{ ucfirst(str_replace('_', ' ', $sub->jenis_soal)) }}</span>
                                </div>

                                <div class="question-text">{{ $sub->pertanyaan }}</div>
                                
                                @if($sub->gambar_pertanyaan)
                                    <img src="{{ asset('storage/' . $sub->gambar_pertanyaan) }}" class="question-image" alt="Gambar Soal">
                                @endif

                                <div class="answers-grid">
                                    @if($sub->jenis_soal == 'pilihan_ganda')
                                        @foreach($sub->pilihanJawaban as $pilihan)
                                            <div class="answer-item {{ $pilihan->is_benar ? 'correct' : '' }}">
                                                <div class="answer-label">{{ $pilihan->label }}</div>
                                                <div class="answer-content">
                                                    <div class="answer-text">{{ $pilihan->teks_jawaban }}</div>
                                                    @if($pilihan->gambar_jawaban)
                                                        <img src="{{ asset('storage/' . $pilihan->gambar_jawaban) }}" class="answer-image" alt="Gambar Jawaban">
                                                    @endif
                                                </div>
                                                @if($pilihan->is_benar)
                                                    <span class="material-symbols-outlined" style="color: #22c55e;">check_circle</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    @elseif($sub->jenis_soal == 'benar_salah' || $sub->jenis_soal == 'mcma')
                                        @foreach($sub->pilihanJawaban as $pilihan)
                                            <div class="answer-item {{ $pilihan->is_benar ? 'correct' : '' }}">
                                                <div class="answer-label">{{ $pilihan->label }}</div>
                                                <div class="answer-content">
                                                    <div class="answer-text">{{ $pilihan->teks_jawaban }}</div>
                                                    <div style="font-size: 13px; color: #666; margin-top: 4px;">
                                                        Kunci: <strong>{{ $pilihan->is_benar ? 'Benar' : 'Salah' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <!-- Isian / Uraian -->
                                        <div class="answer-item correct">
                                            <div class="answer-content">
                                                <strong style="display: block; margin-bottom: 4px; color: #15803d;">Kunci Jawaban:</strong>
                                                <div class="answer-text">{{ $sub->kunci_jawaban }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($sub->pembahasan)
                                    <div class="explanation">
                                        <div class="explanation-title">
                                            <span class="material-symbols-outlined" style="font-size: 18px;">lightbulb</span>
                                            Pembahasan
                                        </div>
                                        <div>{{ $sub->pembahasan }}</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <!-- Fallback for Single Soal (Old Structure) -->
                        <div class="question-block">
                            <div class="question-header">
                                <span class="question-number">Pertanyaan</span>
                            </div>
                            <div class="question-text">{{ $soal->pertanyaan }}</div>
                            @if($soal->gambar_pertanyaan)
                                <img src="{{ asset('storage/' . $soal->gambar_pertanyaan) }}" class="question-image" alt="Gambar Soal">
                            @endif

                            <div class="answers-grid">
                                @foreach($soal->pilihanJawaban as $pilihan)
                                    <div class="answer-item {{ $pilihan->is_benar ? 'correct' : '' }}">
                                        <div class="answer-label">{{ $pilihan->label }}</div>
                                        <div class="answer-content">
                                            <div class="answer-text">{{ $pilihan->teks_jawaban }}</div>
                                            @if($pilihan->gambar_jawaban)
                                                <img src="{{ asset('storage/' . $pilihan->gambar_jawaban) }}" class="answer-image" alt="Gambar Jawaban">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if($soal->pembahasan)
                                <div class="explanation">
                                    <div class="explanation-title">Pembahasan</div>
                                    <div>{{ $soal->pembahasan }}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</body>
</html>
