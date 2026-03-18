<?php

use App\Http\Controllers\AdministrationController;
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
Route::get('/gfa/admin', function () {
    $wifiSsid = config('app.name', 'Dakar Terminal') . '_WiFi';
    $wifiPassword = '';
    $wifiQrData = sprintf(
        'WIFI:T:%s;S:%s;P:%s;;',
        $wifiPassword !== '' ? 'WPA' : 'nopass',
        $wifiSsid,
        $wifiPassword
    );

    return view('facturation.public-gfa-admin', [
        'wifiSsid' => $wifiSsid,
        'wifiQrData' => $wifiQrData,
    ]);
})->name('facturation.gfa-admin.public');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('administration', [AdministrationController::class, 'index'])->name('administration.index');
    Route::post('administration/roles', [AdministrationController::class, 'storeRole'])->name('administration.roles.store');
    Route::put('administration/roles/{role}', [AdministrationController::class, 'updateRole'])->name('administration.roles.update');
    Route::delete('administration/roles/{role}', [AdministrationController::class, 'destroyRole'])->name('administration.roles.destroy');
    Route::post('administration/users', [AdministrationController::class, 'storeUser'])->name('administration.users.store');
    Route::put('administration/users/{user}', [AdministrationController::class, 'updateUser'])->name('administration.users.update');
    Route::delete('administration/users/{user}', [AdministrationController::class, 'destroyUser'])->name('administration.users.destroy');
    Route::view('facturation/dashboard', 'facturation.dashboard')->name('facturation.dashboard');
    Route::view('facturation/validations', 'facturation.validations')->name('facturation.validations');
    Route::view('facturation/remises', 'facturation.remises')->name('facturation.remises');
    Route::view('facturation/unify', 'facturation.unify')->name('facturation.unify');
    Route::view('facturation/ies', 'facturation.ies')->name('facturation.ies');
    Route::view('facturation/gfa-admin', 'facturation.gfa-admin')->name('facturation.gfa-admin');
});

require __DIR__.'/settings.php';
