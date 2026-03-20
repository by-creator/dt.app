<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('planification')->group(function () {
    Route::view('dashboard', 'direction.planification')->name('planification.dashboard');
});
