<?php

use App\Http\Controllers\portal\HomePortalController;

Route::get('/', [HomePortalController::class, 'index'])->name('prt.home.index');
