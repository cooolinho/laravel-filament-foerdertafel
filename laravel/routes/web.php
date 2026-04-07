<?php

use App\Http\Controllers\DocumentViewController;
use App\Http\Controllers\InquiryAttachmentController;
use App\Http\Controllers\RentalContentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Öffentliche Dokumentenansicht (nur is_public oder AGB-Dokument aus Einstellungen)
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/{document:file_name}', [DocumentViewController::class, 'show'])->name('show');
    Route::get('/{document:file_name}/file', [DocumentViewController::class, 'file'])->name('file');
});

// Rental Content Management Routes (öffentlich zugänglich)
Route::prefix('rental-content')->name('rental.content.')->group(function () {
    Route::get('/access', [RentalContentController::class, 'showAccessForm'])->name('access-form');
    Route::post('/access', [RentalContentController::class, 'accessWithCode'])->name('access');
    Route::get('/access/{code}', [RentalContentController::class, 'accessWithCode'])->name('access.code');
    Route::get('/manage/{code}', [RentalContentController::class, 'manage'])->name('manage');
    Route::post('/manage/{code}', [RentalContentController::class, 'update'])->name('update');
    Route::delete('/manage/{code}/logo', [RentalContentController::class, 'deleteLogo'])->name('delete-logo');
});

// Inquiry-Anhänge (nur für eingeloggte Admin-Nutzer)
Route::get('/inquiry-attachments/{inquiry}/{filename}', [InquiryAttachmentController::class, 'download'])
    ->name('inquiry.attachment.download')
    ->middleware('auth');
