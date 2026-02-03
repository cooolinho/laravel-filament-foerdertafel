<?php

use App\Http\Controllers\RentalContentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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

