<?php

use App\Http\Controllers\web\backend\dashboard\DashboardTpuController;
use App\Http\Controllers\web\backend\tpu\TpuDatasController;
use App\Http\Controllers\web\backend\tpu\TpuKategoriDokumenController;
use App\Http\Controllers\web\backend\tpu\TpuLahanController;
use App\Http\Controllers\web\backend\tpu\TpuMakamController;
use App\Http\Controllers\web\backend\tpu\TpuPetugasController;
use App\Http\Controllers\web\backend\tpu\TpuRefJenisSarprasController;
use App\Http\Controllers\web\backend\tpu\TpuRefStatusMakamController;
use App\Http\Controllers\web\backend\tpu\TpuSarprasController;

/*
|--------------------------------------------------------------------------
| MODUL TPU
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'tpu'], function () {
    // Dashboard TPU
    Route::group(['middleware' => ['Admin']], function () {
        Route::get('/', [DashboardTpuController::class, 'index'])->name('tpu.dashboard.index');
    });

    // Middleware: Admin untuk semua fitur pengelolaan data
    Route::group(['middleware' => ['Admin']], function () {
        // Master Data
        Route::group(['prefix' => 'master'], function () {
            Route::group(['prefix' => 'kategori-dokumen'], function () {
                Route::get('/', [TpuKategoriDokumenController::class, 'index'])->name('tpu.kategori-dokumen.index');
                Route::get('/create', [TpuKategoriDokumenController::class, 'create'])->name('tpu.kategori-dokumen.create');
                Route::post('/create', [TpuKategoriDokumenController::class, 'store'])->name('tpu.kategori-dokumen.store');
                Route::get('/edit/{uuid}', [TpuKategoriDokumenController::class, 'edit'])->name('tpu.kategori-dokumen.edit');
                Route::put('/edit/{uuid}', [TpuKategoriDokumenController::class, 'update'])->name('tpu.kategori-dokumen.update');
                Route::post('/status', [TpuKategoriDokumenController::class, 'status'])->name('tpu.kategori-dokumen.status');
                Route::post('/delete', [TpuKategoriDokumenController::class, 'destroy'])->name('tpu.kategori-dokumen.destroy');
                // New bulk operation routes
                Route::post('/bulk-destroy', [TpuKategoriDokumenController::class, 'bulkDestroy'])->name('tpu.kategori-dokumen.destroy.bulk');
                Route::post('/bulk-status', [TpuKategoriDokumenController::class, 'bulkStatus'])->name('tpu.kategori-dokumen.status.bulk');
            });

            // Manajemen Referensi Status Makam
            Route::group(['prefix' => 'ref-status-makam'], function () {
                Route::get('/', [TpuRefStatusMakamController::class, 'index'])->name('tpu.ref-status-makam.index');
                Route::get('/create', [TpuRefStatusMakamController::class, 'create'])->name('tpu.ref-status-makam.create');
                Route::post('/create', [TpuRefStatusMakamController::class, 'store'])->name('tpu.ref-status-makam.store');
                Route::get('/edit/{uuid}', [TpuRefStatusMakamController::class, 'edit'])->name('tpu.ref-status-makam.edit');
                Route::put('/edit/{uuid}', [TpuRefStatusMakamController::class, 'update'])->name('tpu.ref-status-makam.update');
                Route::post('/status', [TpuRefStatusMakamController::class, 'status'])->name('tpu.ref-status-makam.status');
                Route::post('/delete', [TpuRefStatusMakamController::class, 'destroy'])->name('tpu.ref-status-makam.destroy');
                // New bulk operation routes
                Route::post('/bulk-destroy', [TpuRefStatusMakamController::class, 'bulkDestroy'])->name('tpu.ref-status-makam.destroy.bulk');
                Route::post('/bulk-status', [TpuRefStatusMakamController::class, 'bulkStatus'])->name('tpu.ref-status-makam.status.bulk');
            });

            // Manajemen Referensi Jenis Sarpras
            Route::group(['prefix' => 'ref-jenis-sarpras'], function () {
                Route::get('/', [TpuRefJenisSarprasController::class, 'index'])->name('tpu.ref-jenis-sarpras.index');
                Route::get('/create', [TpuRefJenisSarprasController::class, 'create'])->name('tpu.ref-jenis-sarpras.create');
                Route::post('/create', [TpuRefJenisSarprasController::class, 'store'])->name('tpu.ref-jenis-sarpras.store');
                Route::get('/edit/{uuid}', [TpuRefJenisSarprasController::class, 'edit'])->name('tpu.ref-jenis-sarpras.edit');
                Route::put('/edit/{uuid}', [TpuRefJenisSarprasController::class, 'update'])->name('tpu.ref-jenis-sarpras.update');
                Route::post('/status', [TpuRefJenisSarprasController::class, 'status'])->name('tpu.ref-jenis-sarpras.status');
                Route::post('/delete', [TpuRefJenisSarprasController::class, 'destroy'])->name('tpu.ref-jenis-sarpras.destroy');
                // New bulk operation routes
                Route::post('/bulk-destroy', [TpuRefJenisSarprasController::class, 'bulkDestroy'])->name('tpu.ref-jenis-sarpras.destroy.bulk');
                Route::post('/bulk-status', [TpuRefJenisSarprasController::class, 'bulkStatus'])->name('tpu.ref-jenis-sarpras.status.bulk');
            });

            // Manajemen TPU
            Route::group(['prefix' => 'datas'], function () {
                Route::get('/', [TpuDatasController::class, 'index'])->name('tpu.datas.index');
                Route::get('/create', [TpuDatasController::class, 'create'])->name('tpu.datas.create');
                Route::post('/create', [TpuDatasController::class, 'store'])->name('tpu.datas.store');
                Route::get('/edit/{uuid}', [TpuDatasController::class, 'edit'])->name('tpu.datas.edit');
                Route::put('/edit/{uuid}', [TpuDatasController::class, 'update'])->name('tpu.datas.update');
                Route::post('/delete', [TpuDatasController::class, 'destroy'])->name('tpu.datas.destroy');
                Route::post('/bulk-destroy', [TpuDatasController::class, 'bulkDestroy'])->name('tpu.datas.destroy.bulk');
                // Route dokumen pendukung
                Route::group(['prefix' => '{uuid}/dokumen'], function () {
                    Route::post('/upload', [TpuDatasController::class, 'uploadDokumen'])->name('tpu.datas.dokumen.upload');
                    Route::delete('/{dokumen_uuid}', [TpuDatasController::class, 'deleteDokumen'])->name('tpu.datas.dokumen.delete');
                    Route::get('/download/{dokumen_uuid}', [TpuDatasController::class, 'downloadDokumen'])->name('tpu.datas.dokumen.download');
                });
            });
        });

        // Manajemen Lahan
        Route::group(['prefix' => 'lahan'], function () {
            Route::get('/', [TpuLahanController::class, 'index'])->name('tpu.lahan.index');
            Route::get('/create', [TpuLahanController::class, 'create'])->name('tpu.lahan.create');
            Route::post('/create', [TpuLahanController::class, 'store'])->name('tpu.lahan.store');
            Route::get('/edit/{uuid}', [TpuLahanController::class, 'edit'])->name('tpu.lahan.edit');
            Route::put('/edit/{uuid}', [TpuLahanController::class, 'update'])->name('tpu.lahan.update');
            Route::post('/delete', [TpuLahanController::class, 'destroy'])->name('tpu.lahan.destroy');
            Route::post('/bulk-destroy', [TpuLahanController::class, 'bulkDestroy'])->name('tpu.lahan.destroy.bulk');
            // Route dokumen pendukung
            Route::group(['prefix' => '{uuid}/dokumen'], function () {
                Route::post('/upload', [TpuLahanController::class, 'uploadDokumen'])->name('tpu.lahan.dokumen.upload');
                Route::delete('/{dokumen_uuid}', [TpuLahanController::class, 'deleteDokumen'])->name('tpu.lahan.dokumen.delete');
                Route::get('/download/{dokumen_uuid}', [TpuLahanController::class, 'downloadDokumen'])->name('tpu.lahan.dokumen.download');
            });
        });

        // Manajemen Makam
        Route::group(['prefix' => 'makam'], function () {
            Route::get('/', [TpuMakamController::class, 'index'])->name('tpu.makam.index');
            Route::get('/create', [TpuMakamController::class, 'create'])->name('tpu.makam.create');
            Route::post('/create', [TpuMakamController::class, 'store'])->name('tpu.makam.store');
            Route::get('/edit/{uuid}', [TpuMakamController::class, 'edit'])->name('tpu.makam.edit');
            Route::put('/edit/{uuid}', [TpuMakamController::class, 'update'])->name('tpu.makam.update');
            Route::post('/delete', [TpuMakamController::class, 'destroy'])->name('tpu.makam.destroy');
            Route::post('/bulk-destroy', [TpuMakamController::class, 'bulkDestroy'])->name('tpu.makam.destroy.bulk');
            Route::post('/calculate-kapasitas', [TpuMakamController::class, 'calculateKapasitasAjax'])->name('tpu.makam.calculate-kapasitas');
            Route::post('/lahan-details', [TpuMakamController::class, 'getLahanDetails'])->name('tpu.makam.lahan-details');
            Route::get('/lahan-by-tpu', [TpuMakamController::class, 'getLahanByTpu'])->name('tpu.makam.lahan-by-tpu');
        });

        // Manajemen Petugas
        Route::group(['prefix' => 'petugas'], function () {
            Route::get('/', [TpuPetugasController::class, 'index'])->name('tpu.petugas.index');
            Route::get('/create', [TpuPetugasController::class, 'create'])->name('tpu.petugas.create');
            Route::post('/create', [TpuPetugasController::class, 'store'])->name('tpu.petugas.store');
            Route::get('/edit/{uuid}', [TpuPetugasController::class, 'edit'])->name('tpu.petugas.edit');
            Route::put('/edit/{uuid}', [TpuPetugasController::class, 'update'])->name('tpu.petugas.update');
            Route::post('/status', [TpuPetugasController::class, 'status'])->name('tpu.petugas.status');
            Route::post('/delete', [TpuPetugasController::class, 'destroy'])->name('tpu.petugas.destroy');
            // New bulk operation routes
            Route::post('/bulk-destroy', [TpuPetugasController::class, 'bulkDestroy'])->name('tpu.petugas.destroy.bulk');
            Route::post('/bulk-status', [TpuPetugasController::class, 'bulkStatus'])->name('tpu.petugas.status.bulk');
        });

        // Manajemen Sarpras
        Route::group(['prefix' => 'sarpras'], function () {
            Route::get('/', [TpuSarprasController::class, 'index'])->name('tpu.sarpras.index');
            Route::get('/create', [TpuSarprasController::class, 'create'])->name('tpu.sarpras.create');
            Route::post('/create', [TpuSarprasController::class, 'store'])->name('tpu.sarpras.store');
            Route::get('/edit/{uuid}', [TpuSarprasController::class, 'edit'])->name('tpu.sarpras.edit');
            Route::put('/edit/{uuid}', [TpuSarprasController::class, 'update'])->name('tpu.sarpras.update');
            Route::post('/delete', [TpuSarprasController::class, 'destroy'])->name('tpu.sarpras.destroy');
            Route::post('/bulk-destroy', [TpuSarprasController::class, 'bulkDestroy'])->name('tpu.sarpras.destroy.bulk');
            Route::get('/lahans', [TpuSarprasController::class, 'getLahansByTpu'])->name('tpu.sarpras.lahans');
            // Route dokumen pendukung
            Route::group(['prefix' => '{uuid}/dokumen'], function () {
                Route::post('/upload', [TpuSarprasController::class, 'uploadDokumen'])->name('tpu.sarpras.dokumen.upload');
                Route::delete('/{dokumen_uuid}', [TpuSarprasController::class, 'deleteDokumen'])->name('tpu.sarpras.dokumen.delete');
                Route::get('/download/{dokumen_uuid}', [TpuSarprasController::class, 'downloadDokumen'])->name('tpu.sarpras.dokumen.download');
            });
        });
    });
});