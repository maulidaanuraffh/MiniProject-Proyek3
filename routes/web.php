<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolDistributionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/schools/{school}/distributions', [SchoolDistributionController::class, 'index'])
        ->name('schools.distributions');
    Route::get('/reports/{report}/evidence', [ReportController::class, 'evidence'])
        ->name('reports.evidence');
    });

Route::middleware(['auth', 'role:pelapor'])->group(function () {
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});

Route::middleware(['auth', 'role:pemantau,koordinator'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/rekap', [RecapController::class, 'index'])->name('recap.index');
    });

Route::middleware(['auth', 'role:pemantau'])->group(function () {
    Route::post('/reports/{report}/review', [ReportController::class, 'review'])->name('reports.review');
});

Route::middleware(['auth', 'role:koordinator'])->group(function () {
    Route::post('/reports/{report}/forward', [ReportController::class, 'forward'])->name('reports.forward');
    Route::post('/reports/{report}/close', [ReportController::class, 'close'])->name('reports.close');
});


