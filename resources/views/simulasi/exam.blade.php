<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $simulasi->mataPelajaran->nama }} - {{ $simulasi->nama_simulasi }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
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
            padding-top: 60px;
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
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
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
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        /* Content Area */
        .content-area {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Question Card */
        .question-card {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
            height: auto;
            margin: 20px 0;
            border-radius: 8px;
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
            border-color: #1e3a8a;
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
            background: #1e3a8a;
            color: white;
        }

        .answer-text {
            flex: 1;
            font-size: 15px;
            line-height: 1.6;
            color: #333;
            padding-top: 4px;
        }

        .answer-image {
            max-width: 200px;
            height: auto;
            margin-top: 8px;
            border-radius: 6px;
            cursor: zoom-in;
            transition: transform 0.2s;
        }

        .question-image {
            cursor: zoom-in;
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
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #e5e7eb;
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
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
        }

        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
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
            border-color: #1e3a8a;
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

        .status-box.active {
            background: #3b82f6;
            border-color: #1e3a8a;
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

            .question-card {
                padding: 20px;
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
                <div class="navbar-title">PUSMENDIK</div>
                <div style="font-size: 11px; opacity: 0.9;">APLIKASI ANBK</div>
            </div>
        </div>
        <div class="navbar-info">
            <div class="student-info">
                <div class="student-name">{{ $student->nisn }} - {{ strtoupper($student->name) }}</div>
                <div class="student-nisn">{{ $student->rombongan_belajar }}</div>
            </div>
            <button class="logout-btn" onclick="confirmLogout()">
                <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                Keluar
            </button>
        </div>
    </nav>

    <!-- Exam Header -->
    <div class="exam-header">
        <div>
            <div class="exam-title">{{ $simulasi->nama_simulasi }}</div>
            <div class="exam-subject">{{ $simulasi->mataPelajaran->nama }} - SD Sederajat</div>
        </div>
        <div class="exam-timer">
            <div class="timer-display" id="timerDisplay">
                <span class="material-symbols-outlined">schedule</span>
                <span id="timeRemaining">Sisa Waktu: <strong>{{ $simulasi->durasi_menit }}:00</strong></span>
            </div>
            <button class="btn-daftar-soal" onclick="openDaftarSoal()">
                <span class="material-symbols-outlined">list</span>
                Daftar Soal
            </button>
        </div>
    </div>

    <!-- Content Area -->
    <div class="content-area">
        <div class="question-card">
            <div class="question-header">
                <div class="question-number">Soal nomor <span id="currentQuestionNumber">1</span></div>
                <div class="font-size-controls">
                    <span class="font-size-label">Ukuran font soal:</span>
                    <button class="font-btn small" onclick="changeFontSize('small')">A</button>
                    <button class="font-btn medium" onclick="changeFontSize('medium')">A</button>
                    <button class="font-btn large" onclick="changeFontSize('large')">A</button>
                </div>
            </div>

            <div id="questionContent">
                <!-- Question content will be loaded here dynamically -->
            </div>

            <div class="navigation-buttons">
                <button class="nav-btn btn-prev" id="btnPrev" onclick="prevQuestion()">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Soal sebelumnya
                </button>
                <button class="nav-btn btn-next" id="btnNext" onclick="nextQuestion()">
                    Soal berikutnya
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
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
        let currentQuestion = 0;
        let answers = {};
        let timeRemaining = examDuration * 60; // in seconds
        let timerInterval;

        // Load saved answers from session
        const savedAnswers = @json(Session::get('exam_answers', []));
        if (savedAnswers && Object.keys(savedAnswers).length > 0) {
            answers = savedAnswers;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadQuestion(0);
            generateSoalGrid();
            startTimer();
        });

        function loadQuestion(index) {
            currentQuestion = index;
            const soal = soals[index];
            
            document.getElementById('currentQuestionNumber').textContent = index + 1;
            
            let questionHTML = `
                <div class="question-text" id="questionTextContent">
                    ${soal.pertanyaan}
                </div>
                ${soal.gambar_pertanyaan ? `<img src="/storage/${soal.gambar_pertanyaan}" class="question-image" alt="Gambar Soal">` : ''}
                
                <div class="answer-options">
            `;
            
            const options = ['A', 'B', 'C', 'D'];
            soal.pilihan_jawaban.forEach((pilihan, idx) => {
                const letter = options[idx];
                const isSelected = answers[soal.id] === letter;
                
                questionHTML += `
                    <div class="answer-option ${isSelected ? 'selected' : ''}" onclick="selectAnswer(${soal.id}, '${letter}')">
                        <label class="answer-label">
                            <input type="radio" name="answer" value="${letter}" ${isSelected ? 'checked' : ''}>
                            <div class="answer-letter">${letter}</div>
                            <div class="answer-text">
                                ${pilihan.teks_pilihan}
                                ${pilihan.gambar_pilihan ? `<br><img src="/storage/${pilihan.gambar_pilihan}" class="answer-image" alt="Gambar Pilihan ${letter}">` : ''}
                            </div>
                        </label>
                    </div>
                `;
            });
            
            questionHTML += '</div>';
            
            document.getElementById('questionContent').innerHTML = questionHTML;
            
            // Update navigation buttons
            document.getElementById('btnPrev').style.display = index === 0 ? 'none' : 'flex';
            
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
        }

        function prevQuestion() {
            if (currentQuestion > 0) {
                loadQuestion(currentQuestion - 1);
            }
        }

        function nextQuestion() {
            if (currentQuestion < soals.length - 1) {
                loadQuestion(currentQuestion + 1);
            }
        }

        function goToQuestion(index) {
            loadQuestion(index);
            closeDaftarSoal();
        }

        function generateSoalGrid() {
            const grid = document.getElementById('soalGrid');
            grid.innerHTML = '';
            
            soals.forEach((soal, index) => {
                const btn = document.createElement('button');
                btn.className = 'soal-number-btn';
                btn.textContent = index + 1;
                btn.onclick = () => goToQuestion(index);
                grid.appendChild(btn);
            });
        }

        function updateSoalGrid() {
            const buttons = document.querySelectorAll('.soal-number-btn');
            buttons.forEach((btn, index) => {
                btn.classList.remove('answered', 'active');
                
                if (index === currentQuestion) {
                    btn.classList.add('active');
                } else if (answers[soals[index].id]) {
                    btn.classList.add('answered');
                }
            });
        }

        function openDaftarSoal() {
            document.getElementById('modalDaftarSoal').classList.add('active');
        }

        function closeDaftarSoal() {
            document.getElementById('modalDaftarSoal').classList.remove('active');
        }

        function changeFontSize(size) {
            const questionText = document.getElementById('questionTextContent');
            const answerTexts = document.querySelectorAll('.answer-text');
            
            let fontSize = '16px';
            if (size === 'small') fontSize = '14px';
            if (size === 'large') fontSize = '18px';
            
            questionText.style.fontSize = fontSize;
            answerTexts.forEach(text => text.style.fontSize = fontSize);
        }

        function openImageZoom(imageSrc) {
            const modal = document.getElementById('imageZoomModal');
            const zoomedImage = document.getElementById('zoomedImage');
            zoomedImage.src = imageSrc;
            modal.classList.add('active');
        }

        function closeImageZoom(event) {
            if (event) event.stopPropagation();
            const modal = document.getElementById('imageZoomModal');
            modal.classList.remove('active');
        }

        function attachImageZoomListeners() {
            // Add click listeners to all images
            document.querySelectorAll('.question-image, .answer-image').forEach(img => {
                img.addEventListener('click', function(e) {
                    e.preventDefault();
                    openImageZoom(this.src);
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
                    finishExam();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const display = document.getElementById('timeRemaining');
            const timerDisplay = document.getElementById('timerDisplay');
            
            display.innerHTML = `Sisa Waktu: <strong>${minutes}:${seconds.toString().padStart(2, '0')}</strong>`;
            
            // Warning at 10 minutes
            if (timeRemaining <= 600) {
                timerDisplay.classList.add('warning');
            }
        }

        function finishExam() {
            if (confirm('Apakah Anda yakin ingin mengakhiri ujian?')) {
                clearInterval(timerInterval);
                
                // Submit final answers
                fetch('/simulasi/finish-exam', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(() => {
                    window.location.href = '/simulasi/student-dashboard';
                });
            }
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

        // Prevent leaving page accidentally
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = '';
        });
    </script>
</body>
</html>
