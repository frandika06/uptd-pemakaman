<?php
/*
|--------------------------------------------------------------------------
| MODUL PENGATURAN
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\web\backend\dashboard\DashboardSetupController;
use App\Http\Controllers\web\backend\log\AuditTrailController;
use App\Http\Controllers\web\backend\master\SosmedController;
use App\Http\Controllers\web\backend\master\UsersController;
use App\Http\Controllers\web\backend\profile\ProfileController;

Route::group(['prefix' => 'pengaturan'], function () {
    // Dashboard pengaturan
    Route::get('/', [DashboardSetupController::class, 'index'])->name('setup.apps.index');

    // Audit Trail (accessible by all roles)
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('setup.apps.log.index');

    // Middleware: Admin (Super Admin & Admin)
    Route::group(['middleware' => ['Admin']], function () {
        // Portal users
        Route::group(['prefix' => 'users/{tags}'], function () {
            Route::get('/', [UsersController::class, 'index'])->name('prt.apps.mst.users.index');
            Route::get('/create', [UsersController::class, 'create'])->name('prt.apps.mst.users.create');
            Route::post('/create', [UsersController::class, 'store'])->name('prt.apps.mst.users.store');
            Route::get('/edit/{uuid}', [UsersController::class, 'edit'])->name('prt.apps.mst.users.edit');
            Route::put('/edit/{uuid}', [UsersController::class, 'update'])->name('prt.apps.mst.users.update');
            Route::post('/status', [UsersController::class, 'status'])->name('prt.apps.mst.users.status');
            Route::post('/delete', [UsersController::class, 'destroy'])->name('prt.apps.mst.users.destroy');
        });

        // Portal sosmed
        Route::group(['prefix' => 'sosmed'], function () {
            Route::get('/', [SosmedController::class, 'index'])->name('prt.apps.mst.sosmed.index');
            Route::put('/', [SosmedController::class, 'update'])->name('prt.apps.mst.sosmed.update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | MENU PROFILE (Semua Role)
    |--------------------------------------------------------------------------
    */
    // Profile
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [ProfileController::class, 'index'])->name('prt.apps.profile.index');
        Route::put('/', [ProfileController::class, 'update'])->name('prt.apps.profile.update');
    });
});