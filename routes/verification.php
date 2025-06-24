<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Verification\DocumentVerificationController;

Route::middleware(['auth'])->group(function () {
    Route::get('/verification/upload', [DocumentVerificationController::class, 'showUploadForm'])
        ->name('verification.upload');
    Route::post('/verification/upload', [DocumentVerificationController::class, 'uploadDocument'])
        ->name('verification.upload.submit');
    Route::get('/verification/pending', [DocumentVerificationController::class, 'pendingVerification'])
        ->name('verification.pending');
});
