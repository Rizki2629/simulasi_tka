<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\SoalController;
use App\Http\Controllers\SimulasiController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

Route::get('/soal', [SoalController::class, 'index'])->name('soal.index');
Route::get('/soal/create', [SoalController::class, 'create'])->name('soal.create');
Route::post('/soal', [SoalController::class, 'store'])->name('soal.store');
Route::get('/soal/{id}', [SoalController::class, 'show'])->name('soal.show');
Route::get('/soal/{id}/edit', [SoalController::class, 'edit'])->name('soal.edit');
Route::put('/soal/{id}', [SoalController::class, 'update'])->name('soal.update');
Route::delete('/soal/{id}', [SoalController::class, 'destroy'])->name('soal.destroy');

Route::get('/simulasi/generate', [SimulasiController::class, 'generateSimulasi'])->name('simulasi.generate');
Route::post('/simulasi/generate', [SimulasiController::class, 'storeSimulasi'])->name('simulasi.store');
Route::get('/simulasi/token', [SimulasiController::class, 'generateToken'])->name('simulasi.token');
Route::post('/simulasi/token/refresh', [SimulasiController::class, 'refreshToken'])->name('simulasi.token.refresh');

// Student Login Routes
Route::get('/simulasi/login', [SimulasiController::class, 'showStudentLogin'])->name('simulasi.login');
Route::post('/simulasi/student-login', [SimulasiController::class, 'studentLogin'])->name('simulasi.student.login');
Route::get('/simulasi/student-dashboard', [SimulasiController::class, 'studentDashboard'])->name('simulasi.student.dashboard');
Route::post('/simulasi/confirm-data', [SimulasiController::class, 'confirmData'])->name('simulasi.confirm.data');
Route::post('/simulasi/update-token', [SimulasiController::class, 'updateToken'])->name('simulasi.update.token');
Route::get('/simulasi/student-logout', [SimulasiController::class, 'studentLogout'])->name('simulasi.student.logout');

// Exam Routes
Route::post('/simulasi/start-exam', [SimulasiController::class, 'startExam'])->name('simulasi.start.exam');
Route::get('/simulasi/exam', [SimulasiController::class, 'examInterface'])->name('simulasi.exam');
Route::post('/simulasi/submit-answer', [SimulasiController::class, 'submitAnswer'])->name('simulasi.submit.answer');
Route::post('/simulasi/finish-exam', [SimulasiController::class, 'finishExam'])->name('simulasi.finish.exam');
