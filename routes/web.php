<?php

use App\Http\Controllers\web\backend\dasahboard\DashboardController;

/*
|--------------------------------------------------------------------------
| PORTAL
|--------------------------------------------------------------------------
 */
require base_path('routes/portal.php');

/*
|--------------------------------------------------------------------------
| BACKEND
|--------------------------------------------------------------------------
 */
Route::group(['middleware' => ['pbh', 'auth', 'LastSeen']], function () {
    Route::group(['prefix' => 'backend'], function () {
        // dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('auth.home');
        // CMS
        require base_path('routes/cms.php');
        // TPU
        require base_path('routes/tpu.php');
        // pengaturan
        require base_path('routes/pengaturan.php');
        // general
        require base_path('routes/general.php');
    });
});