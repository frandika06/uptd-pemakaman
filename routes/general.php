<?php
/*
|--------------------------------------------------------------------------
| MENU HELPDESK
|--------------------------------------------------------------------------
*/
// middleware: Operator

use App\Http\Controllers\web\backend\kotak_pesan\KotakPesanController;
use App\Http\Controllers\web\configs\AjaxController;
use App\Http\Controllers\web\configs\AjaxDatatableController;

Route::group(['middleware' => ['Operator']], function () {
    // helpdesk
    Route::group(['prefix' => 'helpdesk'], function () {
        // portal pesan
        Route::group(['prefix' => 'pesan'], function () {
            Route::get('/', [KotakPesanController::class, 'index'])->name('prt.apps.kotak.pesan.index');
            Route::get('/read/{uuid}', [KotakPesanController::class, 'edit'])->name('prt.apps.kotak.pesan.edit');
            Route::put('/read/{uuid}', [KotakPesanController::class, 'update'])->name('prt.apps.kotak.pesan.update');
            Route::post('/delete', [KotakPesanController::class, 'destroy'])->name('prt.apps.kotak.pesan.destroy');
            // New bulk operation routes
            Route::post('/bulk-destroy', [KotakPesanController::class, 'bulkDestroy'])->name('prt.apps.kotak.pesan.destroy.bulk');
        });
    });
});

/*
|--------------------------------------------------------------------------
| AJAX
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'ajax'], function () {
    // statistic
    Route::group(['prefix' => 'statistic'], function () {
        Route::post('/', [AjaxController::class, 'getStatisticContent'])->name('ajax.get.stats.content');
    });
    // datatable
    Route::group(['prefix' => 'datatable'], function () {
        Route::get('/list-galeri', [AjaxDatatableController::class, 'dataGaleriList'])->name('ajax.dt.galeri.list');
        Route::get('/list-esertifikat', [AjaxDatatableController::class, 'dataEsertifikatList'])->name('ajax.dt.esertifikat.list');
        Route::get('/list-anggota-tanos', [AjaxDatatableController::class, 'dataAnggotaTanos'])->name('ajax.dt.tanos.anggota');
    });
});

/*
|--------------------------------------------------------------------------
| OTHER ROUTES
|--------------------------------------------------------------------------
*/

// unduh
Route::group(['prefix' => 'unduh'], function () {});

// export
Route::group(['prefix' => 'export'], function () {
});