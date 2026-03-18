<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DematFormController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/demat', 'demat.index')->name('demat');
Route::get('/demat/validation', [DematFormController::class, 'validationForm'])->name('demat.validation');
Route::post('/demat/validation', [DematFormController::class, 'submitValidation']);
Route::get('/demat/remise', [DematFormController::class, 'remiseForm'])->name('demat.remise');
Route::post('/demat/remise', [DematFormController::class, 'submitRemise']);
Route::view('/gfa/guichet', 'facturation.public-guichet-gfa')->name('facturation.guichet-gfa.public');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::view('facturation/dashboard', 'facturation.dashboard')->name('facturation.dashboard');
    Route::view('facturation/validations', 'facturation.validations')->name('facturation.validations');
});

require __DIR__.'/settings.php';
