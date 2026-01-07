<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $simulasi->mataPelajaran->nama }} - {{ $simulasi->nama_simulasi }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 12px 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            color: white;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 600;
        }

        .navbar-brand img {
            width: 35px;
            height: 35px;
            background: white;
            border-radius: 50%;
            padding: 4px;
        }

        .navbar-info {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .student-info {
            text-align: right;
            font-size: 13px;
        }

        .student-name {
            font-weight: 600;
        }

        .student-nisn {
            font-size: 11px;
            opacity: 0.9;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Container */
        .main-container {
            padding-top: 0;
            min-height: 100vh;
        }

        /* Exam Header */
        .exam-header {
            background: white;
            padding: 16px 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .exam-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .exam-subject {
            font-size: 13px;
            color: #666;
            margin-top: 2px;
        }

        .exam-timer {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .timer-display {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .timer-display.warning {
            color: #dc2626;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .btn-daftar-soal {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-daftar-soal:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Content Area */
        .content-area {
            padding: 0;
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            height: calc(100vh - 60px);
            overflow: hidden;
            margin-top: 60px;
        }

        .exam-layout {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100%;
            padding: 0;
        }

        /* Question Card */
        .question-card {
            background: white;
            border-radius: 0;
            padding: 36px 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            width: 100%;
            margin: 0;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }

        .question-number {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .font-size-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .font-size-label {
            font-size: 13px;
            color: #666;
            margin-right: 4px;
        }

        .font-btn {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .font-btn:hover {
            background: #e5e7eb;
        }

        .font-btn.small {
            font-size: 12px;
        }

        .font-btn.medium {
            font-size: 14px;
        }

        .font-btn.large {
            font-size: 16px;
        }

        .question-text {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 32px;
        }

        .question-image {
            max-width: 100%;
            max-height: 400px;
            width: auto;
            height: auto;
            object-fit: contain;
            margin: 20px auto;
            display: block;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Answer Options */
        .answer-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .answer-option {
            display: flex;
            align-items: flex-start;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .answer-option:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .answer-option.selected {
            border-color: #2563eb;
            background: #dbeafe;
        }

        .answer-option input[type="radio"] {
            display: none;
        }

        .answer-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            width: 100%;
            cursor: pointer;
        }

        .answer-letter {
            min-width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #666;
            transition: all 0.2s;
        }

        .answer-option.selected .answer-letter {
            background: #2563eb;
            color: white;
        }

        .answer-text {
            flex: 1;
            font-size: 15px;
            line-height: 1.6;
            color: #333;
            padding-top: 4px;
        }
        
        /* Checkbox styling for MCMA */
        .answer-option input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #3b82f6;
            margin-right: 12px;
        }

        .answer-image {
            max-width: 100%;
            max-height: 150px;
            width: auto;
            height: auto;
            object-fit: contain;
            margin-top: 8px;
            border-radius: 6px;
            cursor: zoom-in;
            transition: transform 0.2s;
        }
        
        .answer-image:hover {
            transform: scale(1.05);
        }

        .question-image {
            cursor: zoom-in;
        }
        
        .zoomable-image:hover {
            transform: scale(1.02);
            filter: brightness(0.95);
        }
        
        /* Benar-Salah Table */
        .benar-salah-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 16px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        
        .benar-salah-table thead {
            background: #f8f9fa;
        }
        
        .benar-salah-table th {
            padding: 16px;
            font-weight: 700;
            font-size: 14px;
            text-align: left;
            color: #1e3a8a;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }
        
        .benar-salah-table td {
            padding: 16px;
            font-size: 15px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        
        .benar-salah-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .benar-salah-table tbody tr:hover {
            background-color: #f0f9ff;
        }
        
        .benar-salah-table input[type="radio"] {
            width: 22px;
            height: 22px;
            cursor: pointer;
            accent-color: #2563eb;
            vertical-align: middle;
        }

        /* Responsive Table Container */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Image Zoom Modal */
        .image-zoom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            cursor: zoom-out;
        }

        .image-zoom-modal.active {
            display: flex;
        }

        .image-zoom-container {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .image-zoom-content {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .zoom-close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 10000;
        }

        .zoom-close-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        /* Navigation Buttons */
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #e5e7eb;
            position: relative;
        }

        .nav-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
        }

        .btn-prev {
            background: #6b7280;
            color: white;
        }

        .btn-prev:hover {
            background: #4b5563;
        }

        .btn-next {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
        }

        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-submit {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        /* Modal Daftar Soal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
        }

        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            color: #666;
        }

        .modal-body {
            padding: 24px;
            max-height: calc(80vh - 80px);
            overflow-y: auto;
        }

        .soal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            gap: 12px;
        }

        .soal-number-btn {
            aspect-ratio: 1;
            border: 2px solid #d1d5db;
            background: white;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            color: #666;
        }

        .soal-number-btn:hover {
            transform: scale(1.05);
        }

        .soal-number-btn.answered {
            background: #10b981;
            border-color: #059669;
            color: white;
        }

        .soal-number-btn.active {
            background: #3b82f6;
            border-color: #2563eb;
            color: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }

        /* Info Status */
        .status-info {
            display: flex;
            gap: 24px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 2px solid #e5e7eb;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
        }

        .status-box {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 2px solid #d1d5db;
        }

        .status-box.answered {
            background: #10b981;
            border-color: #059669;
        }
        
        /* Custom scrollbar for question content */
        .question-card > div[style*="overflow-y"]::-webkit-scrollbar,
        div[style*="overflow-y: auto"]::-webkit-scrollbar {
            width: 8px;
        }
        
        .question-card > div[style*="overflow-y"]::-webkit-scrollbar-track,
        div[style*="overflow-y: auto"]::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        
        .question-card > div[style*="overflow-y"]::-webkit-scrollbar-thumb,
        div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .question-card > div[style*="overflow-y"]::-webkit-scrollbar-thumb:hover,
        div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .status-box.active {
            background: #3b82f6;
            border-color: #2563eb;
        }

        .status-box.doubt {
            background: #eab308;
            border-color: #ca8a04;
        }

        .soal-number-btn.doubt {
            background: #fef3c7;
            border-color: #eab308;
            color: #92400e;
        }

        /* Confirmation Modal */
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .confirmation-modal.active {
            display: flex;
        }

        .confirmation-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.3s ease;
        }

        .confirmation-content h3 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            text-align: center;
        }

        .confirmation-content p {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 24px;
            text-align: center;
            line-height: 1.6;
        }

        .confirmation-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .confirmation-buttons button {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-kembali {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-kembali:hover {
            background: #e2e8f0;
        }

        .btn-selesai {
            background: #22c55e;
            color: white;
        }

        .btn-selesai:hover {
            background: #16a34a;
        }

        .btn-lanjutkan {
            background: #ef4444;
            color: white;
        }

        .btn-lanjutkan:hover {
            background: #dc2626;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 12px;
                padding: 12px 16px;
            }

            .navbar-info {
                width: 100%;
                justify-content: space-between;
            }

            .exam-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .exam-timer {
                width: 100%;
                justify-content: space-between;
            }

            .content-area {
                padding: 16px;
            }

            .exam-layout {
                grid-template-columns: 1fr;
            }

            .question-card {
                padding: 20px;
            }

            .sidebar-widget {
                position: static;
            }

            .navigation-buttons {
                flex-direction: column;
                gap: 12px;
            }

            .nav-btn {
                width: 100%;
                justify-content: center;
            }

            .soal-grid {
                grid-template-columns: repeat(auto-fill, minmax(45px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%231e3a8a'/%3E%3Ctext x='50' y='65' text-anchor='middle' fill='white' font-size='40' font-weight='bold'%3ES%3C/text%3E%3C/svg%3E" alt="Logo">
            <div>
                <div class="navbar-title">SDN GROGOL UTARA 09</div>
                <div style="font-size: 11px; opacity: 0.9;">SIMULASI TKA</div>
            </div>
        </div>
        <div class="navbar-info">
            <div class="student-info">
                <div class="student-name">{{ $student->nisn }} - {{ strtoupper($student->name) }} ({{ $student->rombongan_belajar }})</div>
            </div>
            <button class="logout-btn" onclick="confirmLogout()">
                <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                Keluar
            </button>
        </div>
    </nav>

    <!-- Content Area -->
    <div class="content-area">
        <div class="exam-layout">
            <div class="question-card">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; margin: -36px -40px 24px -40px;">
                    <div style="display: flex; align-items: center; gap: 24px;">
                        <button class="btn-daftar-soal" onclick="openDaftarSoal()" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
                            <span class="material-symbols-outlined">apps</span>
                            Daftar Soal
                        </button>
                        <div style="font-size: 15px; font-weight: 600;">{{ $simulasi->mataPelajaran->nama }} - SD Sederajat</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px;">
                            <span class="material-symbols-outlined">schedule</span>
                            <span style="font-weight: 600;">Sisa Waktu :</span>
                            <span id="timeRemaining" style="font-weight: 700; font-size: 16px;">{{ $simulasi->durasi_menit }}:00</span>
                        </div>
                    </div>
                </div>
                
                <div class="question-header">
                    <div class="question-number">Soal nomor <span id="currentQuestionNumber">1</span></div>
                    <div class="font-size-controls">
                        <span class="font-size-label">Ukuran font soal:</span>
                        <button class="font-btn small" onclick="changeFontSize('small')">A</button>
                        <button class="font-btn medium" onclick="changeFontSize('medium')">A</button>
                        <button class="font-btn large" onclick="changeFontSize('large')">A</button>
                    </div>
                </div>

                <div style="flex: 1; overflow-y: auto; overflow-x: hidden; margin: 24px -8px; padding: 0 8px;">
                    <div id="questionContent">
                        <!-- Question content will be loaded here dynamically -->
                    </div>
                </div>

                <div class="navigation-buttons" id="navButtons" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                    <button class="nav-btn btn-prev" id="btnPrev" onclick="prevQuestion()">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Soal sebelumnya
                    </button>
                    <button class="nav-btn btn-submit" id="btnDoubt" style="background: #eab308; border-color: #ca8a04;" onclick="toggleDoubt()">
                        <input type="checkbox" id="checkboxDoubt" style="width: 18px; height: 18px; cursor: pointer; margin-right: 8px;" onclick="event.stopPropagation()">
                        <span id="doubtText">Tandai Ragu-ragu</span>
                    </button>
                    <button class="nav-btn btn-submit" style="background: #16a34a; border-color: #15803d; display: none;" onclick="finishExam()">
                        <span class="material-symbols-outlined">flag</span>
                        Akhiri Ujian
                    </button>
                    <button class="nav-btn btn-next" id="btnNext" onclick="nextQuestion()">
                        Soal berikutnya
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Daftar Soal -->
    <div class="modal" id="modalDaftarSoal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Daftar Soal</div>
                <button class="modal-close" onclick="closeDaftarSoal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="soal-grid" id="soalGrid">
                    <!-- Soal numbers will be generated here -->
                </div>
                <div class="status-info">
                    <div class="status-item">
                        <div class="status-box answered"></div>
                        <span>Sudah dijawab</span>
                    </div>
                    <div class="status-item">
                        <div class="status-box doubt"></div>
                        <span>Ragu-ragu</span>
                    </div>
                    <div class="status-item">
                        <div class="status-box active"></div>
                        <span>Soal aktif</span>
                    </div>
                    <div class="status-item">
                        <div class="status-box"></div>
                        <span>Belum dijawab</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Zoom Modal -->
    <div class="image-zoom-modal" id="imageZoomModal" onclick="closeImageZoom(event)">
        <button class="zoom-close-btn" onclick="closeImageZoom(event)">
            <span class="material-symbols-outlined" style="color: #333;">close</span>
        </button>
        <div class="image-zoom-container" onclick="event.stopPropagation()">
            <img src="" alt="Zoomed Image" class="image-zoom-content" id="zoomedImage">
        </div>
    </div>

    <script>
        // Data soal dari backend
        const soals = @json($soals);
        const examDuration = {{ $simulasi->durasi_menit }};
        const simulasiId = {{ $simulasi->id }};
        const studentId = {{ Session::get('student_id') }};
        const storageKey = `exam_progress_${studentId}_${simulasiId}`;
        
        let currentQuestion = 0;
        let answers = {};
        let doubtQuestions = {}; // Track ragu-ragu questions
        let timeRemaining = examDuration * 60; // in seconds
        let timerInterval;
        let examStartTime = null;

        // Load saved progress from localStorage and session
        function loadSavedProgress() {
            // Try localStorage first (backup)
            const localProgress = localStorage.getItem(storageKey);
            if (localProgress) {
                try {
                    const progress = JSON.parse(localProgress);
                    answers = progress.answers || {};
                    doubtQuestions = progress.doubtQuestions || {};
                    currentQuestion = progress.currentQuestion || 0;
                    timeRemaining = progress.timeRemaining || (examDuration * 60);
                    examStartTime = progress.examStartTime || Date.now();
                    console.log('Progress loaded from localStorage');
                    return true;
                } catch (e) {
                    console.error('Error loading from localStorage:', e);
                }
            }
            
            // Fallback to session
            const savedAnswers = @json(Session::get('exam_answers', []));
            if (savedAnswers && Object.keys(savedAnswers).length > 0) {
                answers = savedAnswers;
                console.log('Answers loaded from session');
            }
            
            examStartTime = Date.now();
            return false;
        }

        // Save progress to both localStorage and session
        function saveProgress() {
            const progress = {
                answers: answers,
                doubtQuestions: doubtQuestions,
                currentQuestion: currentQuestion,
                timeRemaining: timeRemaining,
                examStartTime: examStartTime,
                lastSaved: Date.now()
            };
            
            // Save to localStorage
            try {
                localStorage.setItem(storageKey, JSON.stringify(progress));
            } catch (e) {
                console.error('Error saving to localStorage:', e);
            }
        }
        
        // Auto-save every 5 seconds
        setInterval(saveProgress, 5000);
        
        let isSubmitting = false;
        
        // Save before page unload logic
        window.addEventListener('beforeunload', function(e) {
            if (!isSubmitting) {
                // Save progress
                saveProgress();
                // Standard browser confirmation message (custom text ignored by modern browsers)
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadSavedProgress();
            
            // Validate currentQuestion is within bounds
            if (currentQuestion >= soals.length) {
                currentQuestion = 0;
            }
            if (currentQuestion < 0) {
                currentQuestion = 0;
            }
            
            loadQuestion(currentQuestion);
            generateSoalGrid();
            startTimer();
        });

        // Save single answer immediately (optional sync)
        function saveAnswer(soalId, answer) {
            // Save to localStorage immediately
            saveProgress();
            
            // Optional: Debounced server sync could go here if needed
            // For now, rely on periodic safe or final submit.
            // Or trigger a background sync.
            
            // Send to server in background (silent)
            fetch('/simulasi/save-answer', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                     'Accept': 'application/json'
                 },
                 body: JSON.stringify({
                     soal_id: soalId,
                     jawaban: answer,
                     simulasi_id: simulasiId
                 })
            }).catch(e => console.warn('Background save failed', e));
        }

        function loadQuestion(index) {
            if (index < 0 || index >= soals.length) {
                console.error('Invalid question index:', index);
                return;
            }
            
            currentQuestion = index;
            const soal = soals[index];
            if (!soal) {
                console.error('Question not found at index:', index);
                return;
            }
            
            const soalId = soal.id;
            const jenisSoal = soal.jenis_soal;
            
            // Normalize MCMA answers to array format
            if (jenisSoal === 'mcma' && answers[soalId]) {
                if (typeof answers[soalId] === 'string') {
                    // Convert comma-separated string to array
                    answers[soalId] = answers[soalId].split(',').filter(a => a.trim());
                } else if (!Array.isArray(answers[soalId])) {
                    answers[soalId] = [];
                }
            }
            
            document.getElementById('currentQuestionNumber').textContent = index + 1;
            
            const pilihan = soal.pilihan_jawaban || [];
            
            // Layout: Soal+Gambar di kiri, Pilihan Jawaban di kanan
            let questionHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; height: 100%;">
                    <div style="display: flex; flex-direction: column; gap: 16px; overflow-y: auto;">
                        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                            <div class="question-text" id="questionTextContent">
                                ${soal.pertanyaan || 'Pertanyaan tidak tersedia'}
                            </div>
                        </div>
                        ${soal.gambar_pertanyaan ? `
                            <div style="background: white; border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.08); position: relative;">
                                <div style="position: relative; width: 100%; display: flex; align-items: center; justify-content: center; cursor: zoom-in;" onclick="openImageZoom(event)">
                                    <img src="/storage/${soal.gambar_pertanyaan}" style="max-width: 100%; height: auto; object-fit: contain; border-radius: 8px; transition: transform 0.2s;" class="zoomable-image question-image" alt="Gambar Soal">
                                    <div style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.6); color: white; padding: 8px 12px; border-radius: 6px; display: flex; align-items: center; gap: 6px; font-size: 13px; pointer-events: none;">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">search</span>
                                        <span>Klik untuk memperbesar</span>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); overflow-y: auto;">
                        <div class="answer-options">
            `;
            
            
            // Render Pertanyaan based on Type
            
            // Logika baru untuk Mixed Packet ('grouped')
            if (jenisSoal === 'grouped') {
                if (soal.sub_soal && soal.sub_soal.length > 0) {
                    let isTableOpen = false;
                    
                    soal.sub_soal.forEach((sub, idx) => {
                        const subType = sub.jenis_soal;
                        const isBS = (subType === 'benar_salah');
                        const isMCMA_Item = (subType === 'mcma' || subType === 'pilihan_ganda_kompleks');
                        
                        // Jika item ini BUKAN Benar/Salah, tapi tabel sedang buka -> TUTUP TABEL
                        if (!isBS && isTableOpen) {
                            questionHTML += `</tbody></table></div>`;
                            isTableOpen = false;
                        }
                        if (isBS) {
                            if (!isTableOpen) {
                                questionHTML += `
                                    <div class="table-responsive mb-4" style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
                                        <table class="benar-salah-table" style="margin-top: 0; border: none; border-radius: 0;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60%; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #1e293b; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Pernyataan</th>
                                                    <th class="text-center" style="width: 20%; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #1e293b; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Benar</th>
                                                    <th class="text-center" style="width: 20%; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #1e293b; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Salah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                `;
                                isTableOpen = true;
                            }
                            
                            // Check if this B/S question has multiple options (Complex BS stored in One SubSoal)
                            // OR if it's a simple B/S (statement is the question itself)
                            if (sub.pilihan_jawaban && sub.pilihan_jawaban.length > 0) {
                                sub.pilihan_jawaban.forEach(opt => {
                                    const valB = answers[sub.id] && answers[sub.id][opt.id] === 'B';
                                    const valS = answers[sub.id] && answers[sub.id][opt.id] === 'S';
                                    
                                    questionHTML += `
                                        <tr>
                                            <td style="font-size: 15px; padding: 16px 20px;">${opt.teks_jawaban}</td>
                                            <td class="text-center" style="background: #fafafa;">
                                                <input type="radio" 
                                                       name="bs_${sub.id}_${opt.id}" 
                                                       value="B" 
                                                       ${valB ? 'checked' : ''} 
                                                       onchange="selectBenarSalahOption('${sub.id}', '${opt.id}', 'B')">
                                            </td>
                                            <td class="text-center" style="background: #fafafa;">
                                                <input type="radio" 
                                                       name="bs_${sub.id}_${opt.id}" 
                                                       value="S" 
                                                       ${valS ? 'checked' : ''} 
                                                       onchange="selectBenarSalahOption('${sub.id}', '${opt.id}', 'S')">
                                            </td>
                                        </tr>
                                    `;
                                });
                            } else {
                                // Single statement B/S (Fallback for when no sub-options exist)
                                const savedAnswer = answers[sub.id];
                                questionHTML += `
                                    <tr>
                                        <td style="font-size: 15px; padding: 16px 20px;">${sub.pertanyaan}</td>
                                        <td class="text-center" style="background: #fafafa;">
                                            <input type="radio" 
                                                   name="bs_${sub.id}" 
                                                   value="benar" 
                                                   ${savedAnswer === 'benar' ? 'checked' : ''} 
                                                   onchange="selectBenarSalah('${sub.id}', 'benar')">
                                        </td>
                                        <td class="text-center" style="background: #fafafa;">
                                            <input type="radio" 
                                                   name="bs_${sub.id}" 
                                                   value="salah" 
                                                   ${savedAnswer === 'salah' ? 'checked' : ''} 
                                                   onchange="selectBenarSalah('${sub.id}', 'salah')">
                                        </td>
                                    </tr>
                                `;
                            }
                        } else if (isMCMA_Item) {
                            // Cek apakah MCMA ini memiliki pilihan ganda internal (Standard MCMA nested in group)
                            if (sub.pilihan_jawaban && sub.pilihan_jawaban.length > 0) {
                                // Render Question Text if available
                                if (sub.pertanyaan) {
                                    questionHTML += `<div style="margin-bottom: 16px; font-size: 15px; font-weight: 500; color: #1e293b;">${sub.pertanyaan}</div>`;
                                }
                                
                                sub.pilihan_jawaban.forEach(pil => {
                                    // Handle Answer storage (Array)
                                    const subId = sub.id;
                                    const optionId = pil.id; // Or label? Standard MCMA uses Label/Letter usually, but here likely ID?
                                    // Wait, Standard MCMA uses pil.label or index.
                                    // Let's use pil.id for precision if possible, or label if consistent.
                                    // Existing standard logic uses: `const letter = pil.label || String.fromCharCode(65 + idx);`
                                    // Controller uses `jawaban` column which matches `pilihan_jawaban`.`label` usually.
                                    // Let's stick to Label/Letter for consistency.
                                    
                                    /* However, the user might expect the VALUE to be used.
                                       In `selectBenarSalahOption`, we used ID.
                                       In `handleMCMAChange`, we used Lettern (A, B..).
                                       
                                       If this is a nested MCMA, let's try to align with standard MCMA `startExam` logic.
                                       Pil usually has `label`.
                                    */
                                    
                                    const letter = pil.label || pil.teks_jawaban; // Fallback? Or generate letter?
                                    // Ideally backend provides label A,B,C. If not, maybe use ID?
                                    // Let's use pil.id to be safe for nested complex structures? 
                                    // But PenilaianService needs to check key.
                                    
                                    // Let's check what Standard MCMA does below:
                                    // `const letter = pil.label || String.fromCharCode(65 + idx);`
                                    // We should replicate that.
                                    
                                    // We can't easily get idx inside foreach, let's hope label exists.
                                    // Or assume A,B,C.. based on order.
                                    
                                    // Wait, `sub.pilihan_jawaban` is an array.
                                    // Let's calculate letter.
                                });
                                
                                sub.pilihan_jawaban.forEach((pil, pIdx) => {
                                     const letter = pil.label || String.fromCharCode(65 + pIdx);
                                     const subId = sub.id;
                                     
                                     // Retrieve saved answer
                                     let isSelected = false;
                                     if (answers[subId] && (Array.isArray(answers[subId]) || typeof answers[subId] === 'string')) {
                                         let currentArr = answers[subId];
                                         if (typeof currentArr === 'string') currentArr = currentArr.split(',');
                                         isSelected = currentArr.includes(letter);
                                     }
                                     
                                     // Render Item
                                     questionHTML += `
                                        <div class="answer-option ${isSelected ? 'selected' : ''}" style="margin-bottom: 10px;">
                                            <label class="answer-label" style="cursor: pointer; display: flex; align-items: start;">
                                                <input type="checkbox" 
                                                       name="chk_nested_${subId}" 
                                                       value="${letter}" 
                                                       ${isSelected ? 'checked' : ''} 
                                                       onchange="handleMCMAChange('${subId}', '${letter}', this)">
                                                <div class="answer-text" style="margin-left: 12px; font-size: 15px;">
                                                     <span style="font-weight:600; margin-right:6px;">${letter}.</span> ${pil.teks_jawaban}
                                                     ${pil.gambar_jawaban ? `<br><img src="/storage/${pil.gambar_jawaban}" class="answer-image" alt="Gambar">` : ''}
                                                </div>
                                            </label>
                                        </div>
                                     `;
                                });
                                
                            } else {
                                // Packet Style (SubSoal IS the option - Legacy/Simple Checkbox)
                                const subId = sub.id;
                                const isChecked = answers[subId] === '1' || answers[subId] === 'true' || answers[subId] === 'checked';
                                
                                questionHTML += `
                                    <div class="answer-option ${isChecked ? 'selected' : ''}" style="margin-bottom: 12px; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05); background: white;">
                                        <label class="answer-label" style="cursor: pointer; display: flex; align-items: center; width: 100%;">
                                            <div style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border: 2px solid #d1d5db; border-radius: 6px; margin-right: 16px; background: white; transition: all 0.2s;" class="custom-checkbox">
                                                <input type="checkbox" 
                                                       name="chk_${subId}" 
                                                       value="1" 
                                                       ${isChecked ? 'checked' : ''} 
                                                       style="opacity: 0; position: absolute; cursor: pointer;"
                                                       onchange="selectBenarSalah('${subId}', this.checked ? '1' : ''); toggleCheckboxVisual(this);">
                                                <span class="material-symbols-outlined" style="font-size: 18px; color: white; display: ${isChecked ? 'block' : 'none'};">check</span>
                                            </div>
                                            <div class="answer-text" style="font-size: 15px; color: #374151; font-weight: 500;">
                                                ${sub.pertanyaan}
                                            </div>
                                        </label>
                                    </div>
                                `;
                            }
                        } else {
                            // Fallback for unexpected types in group (e.g. Standard PG)
                            // Render as text? Or Warning?
                            questionHTML += `<div style="padding:10px; border:1px solid red;">Tipe soal tidak didukung dalam paket: ${subType}</div>`;
                        }
                    });
                    
                    // Tutup tabel jika masih terbuka di akhir loop
                    if (isTableOpen) {
                        questionHTML += `</tbody></table></div>`;
                    }
                }
            } 
            else if (jenisSoal === 'benar_salah') {
                // Determine if we should render rows from Sub-Soal (grouped) or Pilihan Jawaban (single complex)
                const hasSubSoal = soal.sub_soal && soal.sub_soal.length > 0;
                const hasOptions = soal.pilihan_jawaban && soal.pilihan_jawaban.length > 0;
                
                questionHTML += `
                    <div class="table-responsive mb-4" style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <table class="benar-salah-table" style="margin-top: 0; width: 100%; border: none; border-radius: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 60%; padding: 16px 20px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #1e293b; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Pernyataan</th>
                                    <th style="width: 20%; padding: 16px; text-align: center; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #1e293b; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Benar</th>
                                    <th style="width: 20%; padding: 16px; text-align: center; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #1e293b; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Salah</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                if (hasSubSoal) {
                    // Legacy Grouped Logic (should be handled by 'grouped' block normally, but keep as fallback)
                     // ... actually 'grouped' block handles this. 
                     // This block is reached for SINGLE Benar/Salah items that were NOT grouped.
                }

                if (hasOptions) {
                    // Logic for Single Question with Multiple Statements in Options
                    // iterate pilihan_jawaban as rows
                    soal.pilihan_jawaban.forEach((pil) => {
                         // We need to store answer per Option ID
                         // answers[soalId] will be an OBJECT { optId: userVal, ... }
                         let currentVal = '';
                         const currentAnsObj = answers[soalId];
                         if (currentAnsObj && typeof currentAnsObj === 'object') {
                             currentVal = currentAnsObj[pil.id];
                         }
                         
                         questionHTML += `
                            <tr>
                                <td>${pil.teks_jawaban}</td>
                                <td style="text-align: center;">
                                    <input type="radio" 
                                           name="pernyataan_${soalId}_${pil.id}" 
                                           value="benar" 
                                           ${currentVal === 'benar' ? 'checked' : ''}
                                           onchange="selectBenarSalahOption('${soalId}', '${pil.id}', 'benar')">
                                </td>
                                <td style="text-align: center;">
                                    <input type="radio" 
                                           name="pernyataan_${soalId}_${pil.id}" 
                                           value="salah" 
                                           ${currentVal === 'salah' ? 'checked' : ''}
                                           onchange="selectBenarSalahOption('${soalId}', '${pil.id}', 'salah')">
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    // True Single Item (Only 1 statement, the question text itself)
                    const pernyataanId = soalId; 
                    const savedAnswer = answers[pernyataanId];
                    questionHTML += `
                        <tr>
                            <td>${soal.pertanyaan || 'Pernyataan'}</td>
                            <td style="text-align: center;">
                                <input type="radio" name="pernyataan_${pernyataanId}" value="benar" ${savedAnswer === 'benar' ? 'checked' : ''} onchange="selectBenarSalah('${pernyataanId}', 'benar')">
                            </td>
                            <td style="text-align: center;">
                                <input type="radio" name="pernyataan_${pernyataanId}" value="salah" ${savedAnswer === 'salah' ? 'checked' : ''} onchange="selectBenarSalah('${pernyataanId}', 'salah')">
                            </td>
                        </tr>
                    `;
                }
                
                questionHTML += `
                        </tbody>
                    </table>
                `;
            } else {
                // Untuk pilihan ganda dan MCMA
                const isMCMA = (jenisSoal === 'mcma' || jenisSoal === 'pilihan_ganda_kompleks');
                
                // Jika MCMA Paket (Checkboxes P1, P3 etc as sub-questions)
                if (isMCMA && soal.sub_soal && soal.sub_soal.length > 0) {
                     soal.sub_soal.forEach((sub, idx) => {
                        const subId = sub.id; // sub_123
                        // MCMA sub-soal usually treated as individual checkboxes that are CORRECT if checked?
                        // Or is it a group? 
                        // Based on user screenshot: "Jawaban Benar: P1,P3". 
                        // This implies options P1, P2, P3 are listed.
                        // User checks keys.
                        
                        // Check if answer is saved (Array or single boolean '1'?)
                        // If logic: "Setiap opsi benar yang dipilih = 1 poin".
                        // This implies user selects THEM.
                        
                        // Wait, if it's MCMA Packet, do we group them into ONE answer array for the PARENT?
                        // OR do we treat each SUB-SOAL as a standalone checkbox?
                        // Controller flattened them before. Now grouped.
                        // PenilaianService expects keys "sub_123".
                        // So each checkbox is INDEPENDENT submission.
                        
                        // Using 'handleMCMAChange' might be wrong if it aggregates into Parent ID.
                        // We should treat each sub-soal as a Tuple (Checked/Unchecked).
                        // BUT `selectBenarSalah` sends explicit value.
                        // For MCMA Item, value is... "True"? Or the ID itself?
                        
                        // Let's assume standard Checkbox behavior for Packet Items:
                        // Each item is a separate "Question" logically, but displayed together.
                        // Answer Key: Truthy.
                        
                        // We need `handleCheckboxItem` that saves "1" or "true" for `sub_123`.
                        // Re-use selectBenarSalah? Value="1"?
                        // PenilaianService checks `!empty($jawabanUser) && $jawabanUser == $subSoal->jawaban_benar`.
                        // If Key is "1" or "True".
                        
                        const isChecked = answers[subId] === '1' || answers[subId] === 'true' || answers[subId] === 'checked';
                        
                        questionHTML += `
                            <div class="answer-option ${isChecked ? 'selected' : ''}" style="margin-bottom: 10px;">
                                <label class="answer-label" style="cursor: pointer; display: flex; align-items: center;">
                                    <input type="checkbox" 
                                           name="chk_${subId}" 
                                           value="1" 
                                           ${isChecked ? 'checked' : ''} 
                                           onchange="selectBenarSalah('${subId}', this.checked ? '1' : '')">
                                    <div class="answer-text" style="margin-left: 12px;">
                                        ${sub.pertanyaan}
                                    </div>
                                </label>
                            </div>
                        `;
                     });
                     
                } else {
                    // Standard Loop for Pilihan Jawaban (Single Question or Old Structure)
                    const inputType = isMCMA ? 'checkbox' : 'radio';
                    
                    pilihan.forEach((pil, idx) => {
                        const letter = pil.label || String.fromCharCode(65 + idx);
                        
                        let isSelected = false;
                        if (isMCMA) {
                            if (!answers[soalId]) answers[soalId] = [];
                            if (typeof answers[soalId] === 'string') {
                                answers[soalId] = answers[soalId].split(',').filter(a => a.trim());
                            }
                            const currentAnswers = answers[soalId];
                            isSelected = Array.isArray(currentAnswers) && currentAnswers.includes(letter);
                        } else {
                            isSelected = answers[soalId] === letter;
                        }
                        
                        if (isMCMA) {
                            questionHTML += `
                                <div class="answer-option ${isSelected ? 'selected' : ''}" data-soal-id="${soalId}" data-answer="${letter}">
                                    <label class="answer-label">
                                        <input type="checkbox" name="answer[]" value="${letter}" ${isSelected ? 'checked' : ''} onchange="handleMCMAChange('${soalId}', '${letter}', this)">
                                        <div class="answer-text" style="margin-left: 8px;">
                                            ${pil.teks_jawaban}
                                            ${pil.gambar_jawaban ? `<br><img src="/storage/${pil.gambar_jawaban}" class="answer-image" alt="Gambar Pilihan ${letter}">` : ''}
                                        </div>
                                    </label>
                                </div>
                            `;
                        } else {
                            questionHTML += `
                                <div class="answer-option ${isSelected ? 'selected' : ''}" 
                                     data-soal-id="${soalId}" 
                                     data-answer="${letter}"
                                     onclick="selectAnswer('${soalId}', '${letter}', this)">
                                    <label class="answer-label">
                                        <input type="radio" name="answer" value="${letter}" ${isSelected ? 'checked' : ''}>
                                        <div class="answer-letter">${letter}</div>
                                        <div class="answer-text">
                                            ${pil.teks_jawaban}
                                            ${pil.gambar_jawaban ? `<br><img src="/storage/${pil.gambar_jawaban}" class="answer-image" alt="Gambar Pilihan ${letter}">` : ''}
                                        </div>
                                    </label>
                                </div>
                            `;
                        }
                    });
                }
            }
            
            questionHTML += `
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('questionContent').innerHTML = questionHTML;
            
            // Update navigation buttons
            const btnPrev = document.getElementById('btnPrev');
            const btnDoubt = document.getElementById('btnDoubt');
            const btnNext = document.getElementById('btnNext');
            const navButtons = document.getElementById('navButtons');
            
            if (index === 0) {
                btnPrev.style.display = 'none';
                // Keep doubt button in center, next button on right
                btnDoubt.style.position = 'absolute';
                btnDoubt.style.left = '50%';
                btnDoubt.style.transform = 'translateX(-50%)';
                btnNext.style.marginLeft = 'auto';
            } else {
                btnPrev.style.display = 'flex';
                // Keep doubt button centered between prev and next
                btnDoubt.style.position = 'absolute';
                btnDoubt.style.left = '50%';
                btnDoubt.style.transform = 'translateX(-50%)';
                btnNext.style.marginLeft = 'auto';
            }
            
            if (index === soals.length - 1) {
                document.getElementById('btnNext').innerHTML = `
                    <span class="material-symbols-outlined">check_circle</span>
                    Selesai
                `;
                document.getElementById('btnNext').className = 'nav-btn btn-submit';
                document.getElementById('btnNext').onclick = finishExam;
            } else {
                document.getElementById('btnNext').innerHTML = `
                    Soal berikutnya
                    <span class="material-symbols-outlined">arrow_forward</span>
                `;
                document.getElementById('btnNext').className = 'nav-btn btn-next';
                document.getElementById('btnNext').onclick = nextQuestion;
            }
            
            updateSoalGrid();
            updateDoubtButton();
            attachImageZoomListeners();
        }

        function selectAnswer(soalId, answer) {
            answers[soalId] = answer;
            
            // Save to backend
            fetch('/simulasi/submit-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    soal_id: soalId,
                    jawaban: answer
                })
            });
            
            // Update UI
            document.querySelectorAll('.answer-option').forEach(opt => opt.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            updateSoalGrid();
            
            // Save progress immediately
            saveProgress();
        }
        
        function handleMCMAChange(soalId, answer, checkbox) {
            // Untuk MCMA (Multiple Choice Multiple Answer), jawaban berupa array
            if (!answers[soalId] || !Array.isArray(answers[soalId])) {
                answers[soalId] = [];
            }
            
            // Convert string to array if needed
            if (typeof answers[soalId] === 'string') {
                answers[soalId] = answers[soalId].split(',').filter(a => a.trim());
            }
            
            const answerOption = checkbox.closest('.answer-option');
            
            if (checkbox.checked) {
                // Tambahkan jawaban jika belum ada
                if (!answers[soalId].includes(answer)) {
                    answers[soalId].push(answer);
                }
                answerOption.classList.add('selected');
            } else {
                // Hapus jawaban
                answers[soalId] = answers[soalId].filter(a => a !== answer);
                answerOption.classList.remove('selected');
            }
            
            // Save to backend
            fetch('/simulasi/submit-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    soal_id: soalId,
                    jawaban: answers[soalId] // kirim array untuk MCMA
                })
            });
            
            updateSoalGrid();
            saveProgress();
        }
        
        function selectBenarSalah(pernyataanId, jawaban) {
            // Simpan jawaban benar/salah untuk setiap pernyataan
            answers[pernyataanId] = jawaban;
            
            // Save to backend
            fetch('/simulasi/submit-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    soal_id: pernyataanId,
                    jawaban: jawaban
                })
            });
            
            updateSoalGrid();
            saveProgress();
        }

        function selectBenarSalahOption(soalId, optionId, jawaban) {
            // Initiate object if not exists
            if (!answers[soalId] || typeof answers[soalId] !== 'object') {
                answers[soalId] = {};
            }
            answers[soalId][optionId] = jawaban;
            
            // Save to backend
            // Note: Controller stores array as comma-separated string if passed as array.
            // For complex BS, we might need to rely on the associative array being handled or just saved.
            fetch('/simulasi/submit-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    soal_id: soalId,
                    jawaban: answers[soalId]
                })
            });
            
            updateSoalGrid();
            saveProgress();
        }

        function prevQuestion() {
            const newIndex = currentQuestion - 1;
            if (newIndex >= 0 && newIndex < soals.length) {
                loadQuestion(newIndex);
            }
        }

        function nextQuestion() {
            const newIndex = currentQuestion + 1;
            if (newIndex >= 0 && newIndex < soals.length) {
                loadQuestion(newIndex);
            }
        }

        function generateSoalGrid() {
            renderGrid('soalGrid', true);
        }

        function renderGrid(elementId, shouldCloseModal) {
            const grid = document.getElementById(elementId);
            if (!grid) return;
            grid.innerHTML = '';
            soals.forEach((soal, index) => {
                const btn = document.createElement('button');
                btn.className = 'soal-number-btn';
                btn.textContent = index + 1;
                btn.onclick = () => goToQuestion(index, shouldCloseModal);
                grid.appendChild(btn);
            });
        }

        function updateSoalGrid() {
            const buttons = document.querySelectorAll('.soal-number-btn');
            buttons.forEach((btn, index) => {
                btn.classList.remove('answered', 'active', 'doubt');
                
                if (index === currentQuestion) {
                    btn.classList.add('active');
                } else if (index < soals.length) {
                    const soal = soals[index];
                    if (!soal) return;
                    
                    const soalId = soal.id;
                    
                    // Check if marked as doubt
                    if (doubtQuestions[soalId]) {
                        btn.classList.add('doubt');
                    }
                    
                    // Cek apakah soal sudah dijawab
                    let hasAnswer = false;
                    
                    if (soal.jenis_soal === 'grouped') {
                        if (soal.sub_soal) {
                             soal.sub_soal.forEach(sub => {
                                 const ans = answers[sub.id];
                                 if (ans) {
                                     if (typeof ans === 'object' && !Array.isArray(ans)) {
                                          if (Object.keys(ans).length > 0) hasAnswer = true;
                                     } else if (Array.isArray(ans)) {
                                          if (ans.length > 0) hasAnswer = true;
                                     } else {
                                          hasAnswer = true;
                                     }
                                 }
                             });
                        }
                    } else if (soal.jenis_soal === 'benar_salah') {
                        // Untuk benar_salah (Nested or Single)
                        const ans = answers[soalId];
                        if (ans && typeof ans === 'object' && !Array.isArray(ans)) {
                             hasAnswer = Object.keys(ans).length > 0;
                        } else {
                             hasAnswer = !!ans; 
                        }
                    } else {
                        // Untuk pilihan ganda dan MCMA
                        const answer = answers[soalId];
                        hasAnswer = answer && (typeof answer === 'string' || (Array.isArray(answer) && answer.length > 0));
                    }
                    
                    if (hasAnswer) {
                        btn.classList.add('answered');
                    }
                }
            });
        }

        function goToQuestion(index, shouldCloseModal = false) {
            if (index < 0 || index >= soals.length) {
                console.error('Cannot navigate to invalid index:', index);
                return;
            }
            
            currentQuestion = index;
            loadQuestion(index);
            saveProgress();
            if (shouldCloseModal) {
                closeDaftarSoal();
            }
        }

        function toggleCheckboxVisual(checkbox) {
            const container = checkbox.parentElement;
            const checkIcon = container.querySelector('.material-symbols-outlined');
            const answerOption = checkbox.closest('.answer-option');
            
            if (checkbox.checked) {
                container.style.backgroundColor = '#2563eb';
                container.style.borderColor = '#2563eb';
                checkIcon.style.display = 'block';
                answerOption.classList.add('selected');
            } else {
                container.style.backgroundColor = 'white';
                container.style.borderColor = '#d1d5db';
                checkIcon.style.display = 'none';
                answerOption.classList.remove('selected');
            }
        }

        function openDaftarSoal() {
            document.getElementById('modalDaftarSoal').classList.add('active');
        }

        function closeDaftarSoal() {
            document.getElementById('modalDaftarSoal').classList.remove('active');
        }

        function toggleDoubt() {
            const soal = soals[currentQuestion];
            if (!soal) return;
            
            const soalId = soal.id;
            const checkbox = document.getElementById('checkboxDoubt');
            
            if (doubtQuestions[soalId]) {
                // Remove doubt mark
                delete doubtQuestions[soalId];
                checkbox.checked = false;
            } else {
                // Add doubt mark
                doubtQuestions[soalId] = true;
                checkbox.checked = true;
            }
            
            updateSoalGrid();
            saveProgress();
        }

        function updateDoubtButton() {
            const soal = soals[currentQuestion];
            if (!soal) return;
            
            const soalId = soal.id;
            const checkbox = document.getElementById('checkboxDoubt');
            
            checkbox.checked = doubtQuestions[soalId] ? true : false;
        }

        function changeFontSize(size) {
            const questionText = document.getElementById('questionTextContent');
            const answerTexts = document.querySelectorAll('.answer-text');
            const tableCells = document.querySelectorAll('.benar-salah-table td');
            
            let fontSize = '16px';
            if (size === 'small') fontSize = '14px';
            if (size === 'large') fontSize = '18px';
            
            if (questionText) {
                questionText.style.fontSize = fontSize;
            }
            answerTexts.forEach(text => text.style.fontSize = fontSize);
            tableCells.forEach(cell => cell.style.fontSize = fontSize);
        }

        function openImageZoom(event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Find the image element
            let imgElement = event.target;
            if (!imgElement.tagName || imgElement.tagName !== 'IMG') {
                imgElement = event.currentTarget.querySelector('img');
            }
            
            if (imgElement && imgElement.src) {
                const modal = document.getElementById('imageZoomModal');
                const zoomedImage = document.getElementById('zoomedImage');
                zoomedImage.src = imgElement.src;
                modal.classList.add('active');
            }
        }

        function closeImageZoom(event) {
            if (event) event.stopPropagation();
            const modal = document.getElementById('imageZoomModal');
            modal.classList.remove('active');
        }

        function attachImageZoomListeners() {
            // Add click listeners to answer images only (question image uses inline onclick)
            document.querySelectorAll('.answer-image').forEach(img => {
                img.style.cursor = 'zoom-in';
                img.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const modal = document.getElementById('imageZoomModal');
                    const zoomedImage = document.getElementById('zoomedImage');
                    zoomedImage.src = this.src;
                    modal.classList.add('active');
                });
            });
        }

        function startTimer() {
            updateTimerDisplay();
            
            timerInterval = setInterval(() => {
                timeRemaining--;
                updateTimerDisplay();
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    localStorage.removeItem(storageKey);
                    alert('Waktu ujian telah habis!');
                    finishExam();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const display = document.getElementById('timeRemaining');
            
            display.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            // Warning at 10 minutes
            if (timeRemaining <= 600) {
                display.style.color = '#dc2626';
            } else {
                display.style.color = 'white';
            }
        }

        function selectBenarSalah(soalId, value) {
            answers[soalId] = value;
            saveAnswer(soalId, value);
        }

        // Handler untuk Benar/Salah tipe Opsi (Single Question, Many Options)
        // Handler untuk Pilihan Ganda Standard
        // Handler untuk Pilihan Ganda Standard
        function selectAnswer(soalId, answer, element) {
            // 1. Visual Update FIRST (Instant Feedback)
            // Strategy: 
            // - If element passed, use it to find context and siblings.
            // - Fallback to querySelectorAll if no element passed.
            
            let options = [];
            
            if (element) {
                const container = element.parentElement; 
                if (container) {
                     options = container.querySelectorAll('.answer-option');
                }
            }
            
            // Fallback
            if (!options || options.length === 0) {
                 options = document.querySelectorAll(`.answer-option[data-soal-id="${soalId}"]`);
            }

            options.forEach(opt => {
                const radio = opt.querySelector('input[type="radio"]');
                
                // Check match
                const isMatch = (element && opt === element) || (opt.dataset.answer === answer);
                
                if (isMatch) {
                    opt.classList.add('selected');
                    if (radio) radio.checked = true;
                } else {
                    opt.classList.remove('selected');
                }
            });

            // 2. Data Update & Save
            answers[soalId] = answer;
            saveAnswer(soalId, answer);
        }

        function handleMCMAChange(soalId, value, checkbox) {
            let current = answers[soalId] ? answers[soalId] : "";
            // Ensure array logic for MCMA string storage
            let arr = current ? current.split(',') : [];
            arr = arr.filter(x => x.trim().length > 0);

            if (checkbox.checked) {
                if (!arr.includes(value)) arr.push(value);
            } else {
                arr = arr.filter(v => v !== value);
            }

            answers[soalId] = arr.join(',');
            saveAnswer(soalId, answers[soalId]);
            
            // Visual Update
            const optionDiv = checkbox.closest('.answer-option');
            if (optionDiv) {
                if (checkbox.checked) {
                    optionDiv.classList.add('selected');
                } else {
                    optionDiv.classList.remove('selected');
                }
            }
        }

        function selectBenarSalah(soalId, value) {
            answers[soalId] = value;
            saveAnswer(soalId, value);
        }

        function selectBenarSalahOption(soalId, optionId, value) {
            // Initialize if empty
            if (!answers[soalId] || typeof answers[soalId] !== 'object') {
                answers[soalId] = {};
            }
            
            // Update value for specific option
            answers[soalId][optionId] = value;
            
            // Submit the entire object as answer
            saveAnswer(soalId, answers[soalId]);
        }

        function finishExam() {
            // Check for unanswered or doubted questions
            const unansweredCount = soals.filter(soal => !answers[soal.id]).length;

            const doubtCount = Object.keys(doubtQuestions).filter(id => doubtQuestions[id]).length;
            
            const modal = document.getElementById('confirmationModal');
            const title = document.getElementById('confirmTitle');
            const message = document.getElementById('confirmMessage');
            const actionBtn = document.getElementById('confirmActionBtn');
            
            if (unansweredCount > 0 || doubtCount > 0) {
                // Ada soal yang belum dijawab atau ragu-ragu
                title.textContent = 'Peringatan!';
                
                let warningMsg = '';
                if (unansweredCount > 0 && doubtCount > 0) {
                    warningMsg = `Anda masih memiliki ${unansweredCount} soal yang belum dijawab dan ${doubtCount} soal ditandai ragu-ragu. Apakah Anda yakin ingin mengakhiri ujian?`;
                } else if (unansweredCount > 0) {
                    warningMsg = `Anda masih memiliki ${unansweredCount} soal yang belum dijawab. Apakah Anda yakin ingin mengakhiri ujian?`;
                } else {
                    warningMsg = `Anda masih memiliki ${doubtCount} soal yang ditandai ragu-ragu. Apakah Anda yakin ingin mengakhiri ujian?`;
                }
                
                message.textContent = warningMsg;
                actionBtn.textContent = 'Tetap Akhiri Ujian';
                actionBtn.className = 'btn-lanjutkan';
            } else {
                // Semua soal sudah dijawab
                title.textContent = 'Selesaikan Ujian';
                message.textContent = 'Anda telah menjawab semua soal. Apakah Anda yakin ingin menyelesaikan ujian?';
                actionBtn.textContent = 'Selesai Tes';
                actionBtn.className = 'btn-selesai';
            }
            
            modal.classList.add('active');
        }

        function closeConfirmation() {
            document.getElementById('confirmationModal').classList.remove('active');
        }

        function proceedFinish() {
            closeConfirmation();
            clearInterval(timerInterval);
            
            isSubmitting = true; // Prevent unload dialog

            // Show loading
            const modal = document.getElementById('confirmationModal');
            modal.classList.add('active');
            document.getElementById('confirmTitle').textContent = 'Memproses...';
            document.getElementById('confirmMessage').textContent = 'Mohon tunggu, sedang menyimpan hasil ujian Anda.';
            document.querySelector('.confirmation-buttons').style.display = 'none';
            
            // Clear saved progress
            localStorage.removeItem(storageKey);
            
            // Submit final answers and redirect to review page
            fetch('/simulasi/finish-exam', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    answers: answers,
                    time_spent: examDuration * 60 - timeRemaining
                })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Network response was not ok');
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Redirect to review page
                    window.location.href = data.redirect || '/simulasi/review';
                } else {
                    throw new Error(data.message || 'Gagal menyimpan hasil');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal menyimpan hasil: ' + error.message);
                closeConfirmation();
                document.querySelector('.confirmation-buttons').style.display = 'flex';
                isSubmitting = false; // Reset if failed
            });
        }

        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin keluar? Progress ujian akan tersimpan.')) {
                window.location.href = '/simulasi/student-logout';
            }
        }

        // Close modal when clicking outside
        document.getElementById('modalDaftarSoal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDaftarSoal();
            }
        });
    </script>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="confirmation-content">
            <h3 id="confirmTitle">Konfirmasi Tes</h3>
            <p id="confirmMessage">Apakah Anda yakin ingin mengakhiri ujian?</p>
            <div class="confirmation-buttons">
                <button class="btn-kembali" onclick="closeConfirmation()">Kembali</button>
                <button id="confirmActionBtn" class="btn-selesai" onclick="proceedFinish()">Selesai Tes</button>
            </div>
        </div>
    </div>
</body>
</html>
