<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RekapNilaiController;
use App\Http\Controllers\SoalController;
use App\Http\Controllers\SimulasiController;
use App\Http\Controllers\FirebaseTestController;

// Public routes - redirect to login if not authenticated
Route::get('/', HomeController::class);

// Login routes - accessible without authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Protected routes - require authentication (Admin/Guru only)
Route::middleware(['auth', 'role:admin,guru'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management Routes
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/bulk-delete', [UserManagementController::class, 'bulkDelete'])->name('users.bulk-delete');

    // Rekap Nilai Routes
    Route::get('/rekap-nilai', [RekapNilaiController::class, 'index'])->name('rekap-nilai.index');
    Route::get('/rekap-nilai/{simulasi}', [RekapNilaiController::class, 'show'])->name('rekap-nilai.show');
    Route::get('/rekap-nilai/{nilai}/review', [RekapNilaiController::class, 'review'])->name('rekap-nilai.review');
    Route::get('/rekap-nilai/{nilai}/download', [RekapNilaiController::class, 'download'])->name('rekap-nilai.download');
    Route::get('/rekap-nilai/export', [RekapNilaiController::class, 'export'])->name('rekap-nilai.export');

    // Soal Routes
    Route::get('/soal', [SoalController::class, 'index'])->name('soal.index');
    Route::get('/soal/create', [SoalController::class, 'create'])->name('soal.create');
    Route::post('/soal', [SoalController::class, 'store'])->name('soal.store');
    Route::post('/soal/upload-paste-image', [SoalController::class, 'uploadPasteImage'])->name('soal.upload.paste.image');
    Route::get('/soal/{id}', [SoalController::class, 'show'])->name('soal.show');
    Route::get('/soal/{id}/edit', [SoalController::class, 'edit'])->name('soal.edit');
    Route::put('/soal/{id}', [SoalController::class, 'update'])->name('soal.update');
    Route::delete('/soal/{id}', [SoalController::class, 'destroy'])->name('soal.destroy');

    // Simulasi Admin Routes
    Route::get('/simulasi/generate', [SimulasiController::class, 'generateSimulasi'])->name('simulasi.generate');
    Route::post('/simulasi/generate', [SimulasiController::class, 'storeSimulasi'])->name('simulasi.store');
    Route::get('/simulasi/generated-active', [SimulasiController::class, 'generatedActive'])->name('simulasi.generated.active');
    Route::post('/simulasi/{simulasi}/stop', [SimulasiController::class, 'stopSimulasi'])->name('simulasi.stop');
    Route::get('/simulasi/token', [SimulasiController::class, 'generateToken'])->name('simulasi.token');
    Route::post('/simulasi/token/refresh', [SimulasiController::class, 'refreshToken'])->name('simulasi.token.refresh');

    // Student Monitoring Routes (Admin Only)
    Route::get('/simulasi/exam-list', [SimulasiController::class, 'examList'])->name('simulasi.exam.list');
    Route::get('/simulasi/{simulasi}/student-status', [SimulasiController::class, 'studentStatus'])->name('simulasi.student.status');
    Route::post('/simulasi/{simulasi}/reset-login/{user}', [SimulasiController::class, 'resetLogin'])->name('simulasi.reset.login');
    Route::post('/simulasi/{simulasi}/reset-progress/{user}', [SimulasiController::class, 'resetProgress'])->name('simulasi.reset.progress');

    // Hasil dan Nilai Routes (Admin Only)
    Route::get('/simulasi/hasil', [SimulasiController::class, 'hasilUjian'])->name('simulasi.hasil');
    Route::get('/simulasi/riwayat-nilai', [SimulasiController::class, 'riwayatNilai'])->name('simulasi.riwayat.nilai');
    Route::get('/simulasi/nilai/{id}', [SimulasiController::class, 'detailNilai'])->name('simulasi.detail.nilai');
});

// Student Login Routes - Public (No authentication required)
Route::get('/simulasi/login', [SimulasiController::class, 'showStudentLogin'])->name('simulasi.login');
Route::post('/simulasi/student-login', [SimulasiController::class, 'studentLogin'])->name('simulasi.student.login')->middleware('throttle:10,1');
Route::get('/simulasi/student-dashboard', [SimulasiController::class, 'studentDashboard'])->name('simulasi.student.dashboard');
Route::post('/simulasi/confirm-data', [SimulasiController::class, 'confirmData'])->name('simulasi.confirm.data');
Route::post('/simulasi/update-token', [SimulasiController::class, 'updateToken'])->name('simulasi.update.token');
Route::post('/simulasi/student-logout', [SimulasiController::class, 'studentLogout'])->name('simulasi.student.logout');

// Exam Routes - Public (Students can access)
Route::post('/simulasi/start-exam', [SimulasiController::class, 'startExam'])->name('simulasi.start.exam');
Route::get('/simulasi/exam', [SimulasiController::class, 'examInterface'])->name('simulasi.exam');
Route::post('/simulasi/submit-answer', [SimulasiController::class, 'submitAnswer'])->name('simulasi.submit.answer');
Route::post('/simulasi/save-answer', [SimulasiController::class, 'submitAnswer'])->name('simulasi.save.answer');
Route::post('/simulasi/finish-exam', [SimulasiController::class, 'finishExam'])->name('simulasi.finish.exam');
Route::get('/simulasi/review', [SimulasiController::class, 'review'])->name('simulasi.review');
Route::post('/simulasi/finish-review', [SimulasiController::class, 'finishReview'])->name('simulasi.finish-review');

// Firebase Test Routes - Protected (testing only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/firebase/test', [FirebaseTestController::class, 'testFirebase'])->name('firebase.test');
    Route::get('/firebase/soal', [FirebaseTestController::class, 'getSoal'])->name('firebase.soal');
    Route::get('/firebase/soal/mapel/{id}', [FirebaseTestController::class, 'getSoalByMapel'])->name('firebase.soal.mapel');
    Route::post('/firebase/soal', [FirebaseTestController::class, 'storeSoal'])->name('firebase.soal.store');
});
