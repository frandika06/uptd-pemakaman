<?php

/*
|--------------------------------------------------------------------------
| MODUL PENGATURAN
|--------------------------------------------------------------------------
*/
// pengaturan

use App\Http\Controllers\web\backend\dasahboard\DashboardCmsController;
use App\Http\Controllers\web\backend\master\SosmedController;
use App\Http\Controllers\web\backend\master\UsersController;
use App\Http\Controllers\web\backend\profile\ProfileController;

Route::group(['prefix' => 'pengaturan'], function () {
    // dashboard
    Route::get('/', [DashboardCmsController::class, 'index'])->name('setup.apps.index');

    // middleware: Admin
    Route::group(['middleware' => ['Admin']], function () {
        // portal users
        Route::group(['prefix' => 'users/{tags}'], function () {
            Route::get('/', [UsersController::class, 'index'])->name('prt.apps.mst.users.index');
            Route::get('/create', [UsersController::class, 'create'])->name('prt.apps.mst.users.create');
            Route::post('/create', [UsersController::class, 'store'])->name('prt.apps.mst.users.store');
            Route::get('/edit/{uuid}', [UsersController::class, 'edit'])->name('prt.apps.mst.users.edit');
            Route::put('/edit/{uuid}', [UsersController::class, 'update'])->name('prt.apps.mst.users.update');
            Route::post('/status', [UsersController::class, 'status'])->name('prt.apps.mst.users.status');
            Route::post('/delete', [UsersController::class, 'destroy'])->name('prt.apps.mst.users.destroy');
        });
        // portal sosmed
        Route::group(['prefix' => 'sosmed'], function () {
            Route::get('/', [SosmedController::class, 'index'])->name('prt.apps.mst.sosmed.index');
            Route::put('/', [SosmedController::class, 'update'])->name('prt.apps.mst.sosmed.update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | MENU PROFILE
    |--------------------------------------------------------------------------
    */
    // profile
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/profile', [ProfileController::class, 'index'])->name('prt.apps.profile.index');
        Route::put('/profile', [ProfileController::class, 'update'])->name('prt.apps.profile.update');
    });

});
