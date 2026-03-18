<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/demat', 'demat.index')->name('demat');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::view('facturation/dashboard', 'facturation.dashboard')->name('facturation.dashboard');
});

require __DIR__.'/settings.php';
