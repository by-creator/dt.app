<?php

use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacturationApiController;
use App\Http\Controllers\FacturationController;
use App\Http\Controllers\GfaDisplayController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\TiersUnifyController;
use App\Http\Controllers\UnifyPrintController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::view('menu', 'menu.index')->name('menu.index');

    Route::get('administration', [AdministrationController::class, 'index'])->name('administration.index');
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('audit/export/csv', [AuditController::class, 'exportCsv'])->name('audit.export.csv');
    Route::get('audit/export/pdf', [AuditController::class, 'exportPdf'])->name('audit.export.pdf');
    Route::post('administration/roles', [AdministrationController::class, 'storeRole'])->name('administration.roles.store');
    Route::put('administration/roles/{role}', [AdministrationController::class, 'updateRole'])->name('administration.roles.update');
    Route::delete('administration/roles/{role}', [AdministrationController::class, 'destroyRole'])->name('administration.roles.destroy');
    Route::post('administration/users', [AdministrationController::class, 'storeUser'])->name('administration.users.store');
    Route::put('administration/users/{user}', [AdministrationController::class, 'updateUser'])->name('administration.users.update');
    Route::delete('administration/users/{user}', [AdministrationController::class, 'destroyUser'])->name('administration.users.destroy');

    Route::view('direction/dashboard', 'direction.dashboard')->name('direction.dashboard');
    Route::view('direction/financiere', 'direction.financiere')->name('direction.financiere');
    Route::view('direction/exploitation', 'direction.exploitation')->name('direction.exploitation');
    Route::view('direction/remises', 'facturation.remises')->name('direction.remises');

    Route::view('planification/dashboard', 'direction.planification')->name('planification.dashboard');

    Route::view('facturation/dashboard', 'facturation.dashboard')->name('facturation.dashboard');
    Route::view('facturation/validations', 'facturation.validations')->name('facturation.validations');
    Route::view('facturation/remises', 'facturation.remises')->name('facturation.remises');
    Route::view('facturation/unify', 'facturation.unify')->name('facturation.unify');
    Route::view('facturation/rapport', 'facturation.rapport')->name('facturation.rapport');
    Route::get('facturation/ies', [FacturationController::class, 'ies'])->name('facturation.ies');
    Route::post('facturation/ies/lien-acces', [FacturationController::class, 'sendIesAccessLink'])->name('facturation.ies.link');
    Route::post('facturation/ies/creation-compte', [FacturationController::class, 'sendIesAccountCreated'])->name('facturation.ies.create');
    Route::post('facturation/ies/reset-password', [FacturationController::class, 'sendIesPasswordReset'])->name('facturation.ies.reset');
    Route::get('facturation/gfa-admin', [GfaDisplayController::class, 'gfaAdmin'])->name('facturation.gfa-admin');
    Route::post('facturation/gfa-admin/wifi-settings', [GfaDisplayController::class, 'saveWifiSettings'])->name('facturation.gfa-admin.wifi-settings');

    Route::get('facturation/api/rapports', [RapportController::class, 'index']);
    Route::post('facturation/api/rapports/import', [RapportController::class, 'import']);
    Route::get('facturation/api/rapports/export', [RapportController::class, 'exportExcel']);
    Route::delete('facturation/api/rapports/{suivi}', [RapportController::class, 'destroy']);
    Route::get('facturation/api/rattachements', [FacturationApiController::class, 'listRattachements'])->name('facturation.api.rattachements.index');
    Route::patch('facturation/api/rattachements/{rattachement}/valider', [FacturationApiController::class, 'validateRattachement'])->name('facturation.api.rattachements.validate');
    Route::patch('facturation/api/rattachements/{rattachement}/rejeter', [FacturationApiController::class, 'rejectRattachement'])->name('facturation.api.rattachements.reject');
    Route::get('facturation/api/remises', [FacturationApiController::class, 'listRemises'])->name('facturation.api.remises.index');
    Route::patch('facturation/api/remises/{rattachement}/valider', [FacturationApiController::class, 'validateRemise'])->name('facturation.api.remises.validate');
    Route::patch('facturation/api/remises/{rattachement}/rejeter', [FacturationApiController::class, 'rejectRemise'])->name('facturation.api.remises.reject');
    Route::post('facturation/api/tiers-unify/save', [TiersUnifyController::class, 'save']);
    Route::get('facturation/api/tiers-unify', [TiersUnifyController::class, 'index']);
    Route::get('facturation/api/tiers-unify/export', [TiersUnifyController::class, 'exportCsv']);
    Route::get('facturation/api/tiers-unify/export/xlsx', [TiersUnifyController::class, 'exportExcel']);
    Route::post('facturation/api/tiers-unify/import', [TiersUnifyController::class, 'import']);
    Route::put('facturation/api/tiers-unify/{tiers}', [TiersUnifyController::class, 'update']);
    Route::delete('facturation/api/tiers-unify/{tiers}', [TiersUnifyController::class, 'destroy']);
    Route::post('facturation/unify/print/fiche', [UnifyPrintController::class, 'printFiche']);
    Route::post('facturation/unify/print/attestation', [UnifyPrintController::class, 'printAttestation']);
});
