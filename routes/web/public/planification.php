<?php

use App\Http\Controllers\PlanificationController;
use Illuminate\Support\Facades\Route;

Route::get('/planification/upload-manifest', [PlanificationController::class, 'showUpload'])->name('planification.upload-manifest');
Route::post('/planification/upload-manifest', [PlanificationController::class, 'storeManifest'])->name('planification.upload-manifest.store');
