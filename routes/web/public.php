<?php

use App\Http\Controllers\DematFormController;
use App\Http\Controllers\FacturationApiController;
use App\Http\Controllers\GfaApiController;
use App\Http\Controllers\GfaDisplayController;
use App\Http\Controllers\PlanificationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/planification/upload-manifest', [PlanificationController::class, 'showUpload'])->name('planification.upload-manifest');
Route::post('/planification/upload-manifest', [PlanificationController::class, 'storeManifest'])->name('planification.upload-manifest.store');

Route::redirect('/register', '/login');
Route::post('/register', fn () => redirect('/login'));

Route::view('/demat', 'demat.index')->name('demat');
Route::view('/demat/paiement', 'demat.paiement')->name('demat.paiement');
Route::get('/demat/validation', [DematFormController::class, 'validationForm'])->name('demat.validation');
Route::post('/demat/validation', [DematFormController::class, 'submitValidation']);
Route::get('/demat/remise', [DematFormController::class, 'remiseForm'])->name('demat.remise');
Route::post('/demat/remise', [DematFormController::class, 'submitRemise']);

Route::view('/gfa/guichet', 'facturation.public-guichet-gfa')->name('facturation.guichet-gfa.public');
Route::get('/gfa/admin', [GfaDisplayController::class, 'display'])->name('facturation.gfa-admin.public');
Route::get('/gfa/display', [GfaDisplayController::class, 'display']);
Route::get('/gfa/ticket/go', [GfaDisplayController::class, 'ticketGo'])->name('gfa.ticket.go');
Route::get('/gfa/ticket', [GfaDisplayController::class, 'ticket'])->name('gfa.ticket');

Route::get('/gfa/api/services', [GfaApiController::class, 'getServices']);
Route::post('/gfa/api/services', [GfaApiController::class, 'createService']);
Route::put('/gfa/api/services/{service}', [GfaApiController::class, 'updateService']);
Route::delete('/gfa/api/services/{service}', [GfaApiController::class, 'deleteService']);
Route::get('/gfa/api/guichets', [GfaApiController::class, 'getGuichets']);
Route::post('/gfa/api/guichets', [GfaApiController::class, 'createGuichet']);
Route::put('/gfa/api/guichets/{guichet}', [GfaApiController::class, 'updateGuichet']);
Route::delete('/gfa/api/guichets/{guichet}', [GfaApiController::class, 'deleteGuichet']);
Route::get('/gfa/api/agents', [GfaApiController::class, 'getAgents']);
Route::post('/gfa/api/agents', [GfaApiController::class, 'createAgent']);
Route::put('/gfa/api/agents/{agent}', [GfaApiController::class, 'updateAgent']);
Route::delete('/gfa/api/agents/{agent}', [GfaApiController::class, 'deleteAgent']);
Route::get('/gfa/api/guichet/{guichetId}/info', [GfaApiController::class, 'getGuichetInfo']);
Route::get('/gfa/api/guichet/{guichetId}/waiting', [GfaApiController::class, 'getWaitingForGuichet']);
Route::get('/gfa/api/guichet/{guichetId}/current', [GfaApiController::class, 'getCurrentForGuichet']);
Route::post('/gfa/api/guichet/call-next', [GfaApiController::class, 'callNextForGuichet']);
Route::post('/gfa/api/guichet/recall', [GfaApiController::class, 'recallTicket']);
Route::patch('/gfa/api/guichet/ticket/{id}/termine', [GfaApiController::class, 'termineTicket']);
Route::patch('/gfa/api/guichet/ticket/{id}/incomplet', [GfaApiController::class, 'incompletTicket']);
Route::patch('/gfa/api/guichet/ticket/{id}/absent', [GfaApiController::class, 'absentTicket']);
Route::get('/gfa/api/stats', [GfaApiController::class, 'getStats']);
Route::get('/gfa/api/tickets', [GfaApiController::class, 'listTickets']);
Route::get('/gfa/api/tickets/export', [GfaApiController::class, 'exportTickets']);
Route::delete('/gfa/api/tickets/truncate', [GfaApiController::class, 'truncateTickets']);
Route::get('/gfa/api/scan-token', [GfaApiController::class, 'generateScanToken']);
Route::post('/gfa/api/tickets', [GfaApiController::class, 'createTicketPublic']);
