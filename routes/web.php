<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DocumentController::class, 'index'])->name('dashboard');

    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/version', [DocumentController::class, 'saveVersion'])->name('documents.version.store');
});

require __DIR__.'/auth.php';
