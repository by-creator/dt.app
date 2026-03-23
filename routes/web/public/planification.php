<?php

use App\Http\Controllers\PlanificationController;
use Illuminate\Support\Facades\Route;

Route::get('/planification/upload-manifest', [PlanificationController::class, 'showUpload'])->name('planification.upload-manifest');
Route::post('/planification/upload-manifest', [PlanificationController::class, 'storeManifest'])->name('planification.upload-manifest.store');
Route::get('/planification/codification/{codification}/download/xlsx', [PlanificationController::class, 'downloadXlsx'])->name('planification.codification.download.xlsx');
Route::get('/planification/codification/{codification}/download/iftmin', [PlanificationController::class, 'downloadIftmin'])->name('planification.codification.download.iftmin');
Route::get('/planification/codification/{codification}/download/manifest', [PlanificationController::class, 'downloadManifest'])->name('planification.codification.download.manifest');
