<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController; // Kita panggil lagi controller profile-nya bro
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Jika sudah login ke dashboard, jika belum langsung lempar ke halaman login
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // --- ROUTE DOKUMEN (VALDIDOCS) ---
    Route::get('/dashboard', [DocumentController::class, 'index'])->name('dashboard');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/version', [DocumentController::class, 'saveVersion'])->name('documents.version.store');
    Route::post('/documents/{document}/autosave', [DocumentController::class, 'autoSave'])->name('documents.autosave');

    // --- ROUTE PROFILE (BAWAAN BREEZE) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';