<?php

use App\Http\Controllers\web\backend\banner\BannerController;
use App\Http\Controllers\web\backend\dasahboard\DashboardCmsController;
use App\Http\Controllers\web\backend\faq\FAQController;
use App\Http\Controllers\web\backend\galeri\GaleriController;
use App\Http\Controllers\web\backend\links\LinksController;
use App\Http\Controllers\web\backend\master\KategoriController;
use App\Http\Controllers\web\backend\master\KategoriSubController;
use App\Http\Controllers\web\backend\pages\HalamanController;
use App\Http\Controllers\web\backend\posts\PostinganController;
use App\Http\Controllers\web\backend\unduhan\UnduhanController;
use App\Http\Controllers\web\backend\video\VideoController;

/*
|--------------------------------------------------------------------------
| MODUL CMS
|--------------------------------------------------------------------------
*/
// cms
Route::group(['prefix' => 'cms'], function () {
    // dashboard
    Route::get('/', [DashboardCmsController::class, 'index'])->name('prt.apps.index');
    /*
    |--------------------------------------------------------------------------
    | MENU MASTER DATA
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'master'], function () {
        // middleware: Editor
        Route::group(['middleware' => ['Editor']], function () {
            // portal kategori
            Route::group(['prefix' => 'kategori'], function () {
                Route::get('/', [KategoriController::class, 'index'])->name('prt.apps.mst.tags.index');
                Route::get('/create', [KategoriController::class, 'create'])->name('prt.apps.mst.tags.create');
                Route::post('/create', [KategoriController::class, 'store'])->name('prt.apps.mst.tags.store');
                Route::get('/edit/{uuid}', [KategoriController::class, 'edit'])->name('prt.apps.mst.tags.edit');
                Route::put('/edit/{uuid}', [KategoriController::class, 'update'])->name('prt.apps.mst.tags.update');
                Route::post('/status', [KategoriController::class, 'status'])->name('prt.apps.mst.tags.status');
                Route::post('/delete', [KategoriController::class, 'destroy'])->name('prt.apps.mst.tags.destroy');
                // New bulk operation routes
                Route::post('/bulk-destroy', [KategoriController::class, 'bulkDestroy'])->name('prt.apps.mst.tags.destroy.bulk');
                Route::post('/bulk-status', [KategoriController::class, 'bulkStatus'])->name('prt.apps.mst.tags.status.bulk');
            });
            // portal kategori-sub
            Route::group(['prefix' => 'kategori-sub/{uuid_tags}'], function () {
                Route::get('/', [KategoriSubController::class, 'index'])->name('prt.apps.mst.tags.sub.index');
                Route::get('/create', [KategoriSubController::class, 'create'])->name('prt.apps.mst.tags.sub.create');
                Route::post('/create', [KategoriSubController::class, 'store'])->name('prt.apps.mst.tags.sub.store');
                Route::get('/edit/{uuid}', [KategoriSubController::class, 'edit'])->name('prt.apps.mst.tags.sub.edit');
                Route::put('/edit/{uuid}', [KategoriSubController::class, 'update'])->name('prt.apps.mst.tags.sub.update');
                Route::post('/status', [KategoriSubController::class, 'status'])->name('prt.apps.mst.tags.sub.status');
                Route::post('/delete', [KategoriSubController::class, 'destroy'])->name('prt.apps.mst.tags.sub.destroy');
                // New bulk operation routes
                Route::post('/bulk-destroy', [KategoriSubController::class, 'bulkDestroy'])->name('prt.apps.mst.tags.sub.destroy.bulk');
                Route::post('/bulk-status', [KategoriSubController::class, 'bulkStatus'])->name('prt.apps.mst.tags.sub.status.bulk');
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | MENU KONTEN INTERNAL
    |--------------------------------------------------------------------------
    */
    // middleware: Editor
    Route::group(['middleware' => ['Editor']], function () {
        // portal halaman
        Route::group(['prefix' => 'halaman/{tags}'], function () {
            Route::get('/', [HalamanController::class, 'index'])->name('prt.apps.page.index');
            Route::get('/create', [HalamanController::class, 'create'])->name('prt.apps.page.create');
            Route::post('/create', [HalamanController::class, 'store'])->name('prt.apps.page.store');
            Route::get('/edit/{uuid}', [HalamanController::class, 'edit'])->name('prt.apps.page.edit');
            Route::put('/edit/{uuid}', [HalamanController::class, 'update'])->name('prt.apps.page.update');
            Route::post('/delete', [HalamanController::class, 'destroy'])->name('prt.apps.page.destroy');
            // New bulk operation routes
            Route::post('/bulk-destroy', [HalamanController::class, 'bulkDestroy'])->name('prt.apps.page.destroy.bulk');
        });
        // portal links
        Route::group(['prefix' => 'links/{tags}'], function () {
            Route::get('/', [LinksController::class, 'index'])->name('prt.apps.links.index');
            Route::get('/create', [LinksController::class, 'create'])->name('prt.apps.links.create');
            Route::post('/create', [LinksController::class, 'store'])->name('prt.apps.links.store');
            Route::get('/edit/{uuid}', [LinksController::class, 'edit'])->name('prt.apps.links.edit');
            Route::put('/edit/{uuid}', [LinksController::class, 'update'])->name('prt.apps.links.update');
            Route::post('/status', [LinksController::class, 'status'])->name('prt.apps.links.status');
            Route::post('/delete', [LinksController::class, 'destroy'])->name('prt.apps.links.destroy');
            // New bulk operation routes
            Route::post('/bulk-destroy', [LinksController::class, 'bulkDestroy'])->name('prt.apps.links.destroy.bulk');
            Route::post('/bulk-status', [LinksController::class, 'bulkStatus'])->name('prt.apps.links.status.bulk');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | MENU KONTEN TEXT
    |--------------------------------------------------------------------------
    */
    // middleware: Penulis
    Route::group(['middleware' => ['Penulis']], function () {
        // portal postingan
        Route::group(['prefix' => 'postingan'], function () {
            Route::get('/', [PostinganController::class, 'index'])->name('prt.apps.post.index');
            Route::get('/create', [PostinganController::class, 'create'])->name('prt.apps.post.create');
            Route::post('/create', [PostinganController::class, 'store'])->name('prt.apps.post.store');
            Route::get('/edit/{uuid}', [PostinganController::class, 'edit'])->name('prt.apps.post.edit');
            Route::put('/edit/{uuid}', [PostinganController::class, 'update'])->name('prt.apps.post.update');
            Route::post('/delete', [PostinganController::class, 'destroy'])->name('prt.apps.post.destroy');
            // New bulk operation routes
            Route::post('/bulk-destroy', [PostinganController::class, 'bulkDestroy'])->name('prt.apps.post.destroy.bulk');
        });
    });
    // middleware: Editor
    Route::group(['middleware' => ['Editor']], function () {
        // portal faq
        Route::group(['prefix' => 'faq'], function () {
            Route::get('/', [FAQController::class, 'index'])->name('prt.apps.faq.index');
            Route::get('/create', [FAQController::class, 'create'])->name('prt.apps.faq.create');
            Route::post('/create', [FAQController::class, 'store'])->name('prt.apps.faq.store');
            Route::get('/edit/{uuid}', [FAQController::class, 'edit'])->name('prt.apps.faq.edit');
            Route::put('/edit/{uuid}', [FAQController::class, 'update'])->name('prt.apps.faq.update');
            Route::post('/delete', [FAQController::class, 'destroy'])->name('prt.apps.faq.destroy');
            Route::post('/status', [FAQController::class, 'status'])->name('prt.apps.faq.status');
            // New bulk operation routes
            Route::post('/bulk-destroy', [FAQController::class, 'bulkDestroy'])->name('prt.apps.faq.destroy.bulk');
            Route::post('/bulk-status', [FAQController::class, 'bulkStatus'])->name('prt.apps.faq.status.bulk');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | MENU KONTEN MEDIA
    |--------------------------------------------------------------------------
    */
    // middleware: Editor
    Route::group(['middleware' => ['Editor']], function () {
        // portal banner
        Route::group(['prefix' => 'banner'], function () {
            Route::get('/', [BannerController::class, 'index'])->name('prt.apps.banner.index');
            Route::get('/create', [BannerController::class, 'create'])->name('prt.apps.banner.create');
            Route::post('/create', [BannerController::class, 'store'])->name('prt.apps.banner.store');
            Route::get('/edit/{uuid}', [BannerController::class, 'edit'])->name('prt.apps.banner.edit');
            Route::put('/edit/{uuid}', [BannerController::class, 'update'])->name('prt.apps.banner.update');
            Route::post('/delete', [BannerController::class, 'destroy'])->name('prt.apps.banner.destroy');
            Route::post('/status', [BannerController::class, 'status'])->name('prt.apps.banner.status');
        });
    });
    // middleware: Penulis
    Route::group(['middleware' => ['Penulis']], function () {
        // portal galeri
        Route::group(['prefix' => 'galeri'], function () {
            Route::get('/', [GaleriController::class, 'index'])->name('prt.apps.galeri.index');
            Route::get('/create', [GaleriController::class, 'create'])->name('prt.apps.galeri.create');
            Route::get('/edit/{uuid}', [GaleriController::class, 'edit'])->name('prt.apps.galeri.edit');
            Route::put('/edit/{uuid}', [GaleriController::class, 'update'])->name('prt.apps.galeri.update');
            Route::post('/delete', [GaleriController::class, 'destroy'])->name('prt.apps.galeri.destroy');
            Route::post('/status', [GaleriController::class, 'status'])->name('prt.apps.galeri.status');
        });
        // portal video
        Route::group(['prefix' => 'video'], function () {
            Route::get('/', [VideoController::class, 'index'])->name('prt.apps.video.index');
            Route::get('/create', [VideoController::class, 'create'])->name('prt.apps.video.create');
            Route::post('/create', [VideoController::class, 'store'])->name('prt.apps.video.store');
            Route::get('/edit/{uuid}', [VideoController::class, 'edit'])->name('prt.apps.video.edit');
            Route::put('/edit/{uuid}', [VideoController::class, 'update'])->name('prt.apps.video.update');
            Route::post('/delete', [VideoController::class, 'destroy'])->name('prt.apps.video.destroy');
        });
        // portal unduhan
        Route::group(['prefix' => 'unduhan'], function () {
            Route::get('/', [UnduhanController::class, 'index'])->name('prt.apps.unduhan.index');
            Route::get('/create', [UnduhanController::class, 'create'])->name('prt.apps.unduhan.create');
            Route::post('/create', [UnduhanController::class, 'store'])->name('prt.apps.unduhan.store');
            Route::get('/edit/{uuid}', [UnduhanController::class, 'edit'])->name('prt.apps.unduhan.edit');
            Route::put('/edit/{uuid}', [UnduhanController::class, 'update'])->name('prt.apps.unduhan.update');
            Route::post('/delete', [UnduhanController::class, 'destroy'])->name('prt.apps.unduhan.destroy');
        });
    });

    // portal statistik
    Route::group(['prefix' => 'statistik'], function () {
        // Route::get('/', [StatistikController::class, 'index'])->name('prt.apps.stat.index');
    });
});