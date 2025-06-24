<?php

use App\Http\Controllers\portal\HomePortalController;
use App\Http\Controllers\web\auth\AuthController;
use App\Http\Controllers\web\configs\AjaxController;
use App\Http\Controllers\web\configs\NocController;

// home
Route::get('/', [HomePortalController::class, 'index'])->name('prt.home.index');

// NOC
Route::get('/noc/{name}', [NocController::class, 'index']);

// AJAX
Route::group(['prefix' => 'ajax'], function () {
    // re-captcha
    Route::get('/re-captcha', [AjaxController::class, 'getCaptcha'])->name('ajax.captcha.get');
});

// auth
Route::group(['prefix' => 'auth'], function () {
    Route::group(['middleware' => ['pbh']], function () {
        // login
        Route::get('/', [AuthController::class, 'index'])->name('auth.login.index');
        // GUEST ONLY
        Route::group(['middleware' => ['pbh', 'guest']], function () {
            Route::group(['prefix' => 'auth'], function () {
                Route::post('/', [AuthController::class, 'store'])->name('auth.login.store');
                Route::get('/google', [AuthController::class, 'redirectToGoogle'])->name('auth.login.google');
                Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.login.google.callback');
            });
        });
        // AUTH
        Route::group(['prefix' => 'auth'], function () {
            Route::get('/logout', [AuthController::class, 'logout'])->name('prt.login.logout');
        });
    });
});