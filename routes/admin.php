<?php

use App\Http\Controllers\web\auth\AuthController;
use App\Http\Controllers\web\backend\banner\BannerController;
use App\Http\Controllers\web\backend\dasahboard\DashboardController;
use App\Http\Controllers\web\backend\data_direktur\DataDirekturController;
use App\Http\Controllers\web\backend\duta_sma\DutaSMAController;
use App\Http\Controllers\web\backend\ebook\EbookController;
use App\Http\Controllers\web\backend\emagazine\EmagazineController;
use App\Http\Controllers\web\backend\esertifikat\EsertifikatController;
use App\Http\Controllers\web\backend\faq\FAQController;
use App\Http\Controllers\web\backend\galeri\GaleriController;
use App\Http\Controllers\web\backend\greeting\GreetingController;
use App\Http\Controllers\web\backend\infografis\InfografisController;
use App\Http\Controllers\web\backend\kotak_pesan\KotakPesanController;
use App\Http\Controllers\web\backend\links\LinksController;
use App\Http\Controllers\web\backend\master\KategoriController;
use App\Http\Controllers\web\backend\master\KategoriSubController;
use App\Http\Controllers\web\backend\master\SetupController;
use App\Http\Controllers\web\backend\master\SosmedController;
use App\Http\Controllers\web\backend\master\UsersController;
use App\Http\Controllers\web\backend\pages\HalamanController;
use App\Http\Controllers\web\backend\posts\PostinganController;
use App\Http\Controllers\web\backend\profile\ProfileController;
use App\Http\Controllers\web\backend\running_text\RunningTextController;
use App\Http\Controllers\web\backend\tanos\TanosController;
use App\Http\Controllers\web\backend\unduhan\UnduhanController;
use App\Http\Controllers\web\backend\video\VideoController;
use App\Http\Controllers\web\configs\AjaxController;
use App\Http\Controllers\web\configs\AjaxDatatableController;

/*
|--------------------------------------------------------------------------
| BACKEND
|--------------------------------------------------------------------------
 */
Route::group(['prefix' => 'backend'], function () {
    // beranda
    Route::get('/', [AuthController::class, 'index'])->name('auth.login.index');

    // NOC
    // Route::get('/noc/{name}', [NocController::class, 'index']);

    // GUEST ONLY
    Route::group(['middleware' => ['pbh', 'guest']], function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::post('/login', [AuthController::class, 'store'])->name('auth.login.store');
            Route::get('/google', [AuthController::class, 'redirectToGoogle'])->name('auth.login.google');
            Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.login.google.callback');
        });
    });

    // AJAX
    Route::group(['prefix' => 'ajax'], function () {
        // re-captcha
        Route::get('/re-captcha', [AjaxController::class, 'getCaptcha'])->name('ajax.captcha.get');
    });

    // flip book dengan cors
    // Route::group(['middleware' => ['customCors']], function () {
    Route::get('/flip/{path}', function ($path) {
        $filePath = storage_path("app/public/$path");

        if (! file_exists($filePath)) {
            abort(404, "File not found.");
        }

        return Response::file($filePath);
    })->where('path', '.*')->name('flip.get');
// });
/*
|--------------------------------------------------------------------------
| AUTH
| - User logged
|--------------------------------------------------------------------------
 */
    Route::group(['middleware' => ['pbh', 'auth', 'LastSeen']], function () {
        // backend
        Route::group(['prefix' => 'backend'], function () {
            // dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('auth.home');

            /*
        |--------------------------------------------------------------------------
        | MENU MASTER DATA
        |--------------------------------------------------------------------------
        */
            Route::group(['prefix' => 'master'], function () {
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
                    // portal setup
                    Route::group(['prefix' => 'pengaturan'], function () {
                        // model hero
                        Route::group(['prefix' => 'model-hero'], function () {
                            Route::get('/', [SetupController::class, 'indexModelHero'])->name('prt.apps.mst.setup.model.hero.index');
                            Route::put('/', [SetupController::class, 'updateModelHero'])->name('prt.apps.mst.setup.model.hero.update');
                        });
                        // hero section
                        Route::group(['prefix' => 'hero-section'], function () {
                            Route::get('/', [SetupController::class, 'indexHeroSection'])->name('prt.apps.mst.setup.hero.section.index');
                            Route::put('/', [SetupController::class, 'updateHeroSection'])->name('prt.apps.mst.setup.hero.section.update');
                            Route::post('/delete', [SetupController::class, 'destroyHeroSection'])->name('prt.apps.mst.setup.hero.section.destroy');
                        });
                    });
                });
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
                    });
                });
            });

            /*
        |--------------------------------------------------------------------------
        | MENU KONTEN DIREKTORAT
        |--------------------------------------------------------------------------
        */
            // middleware: Editor
            Route::group(['middleware' => ['Editor']], function () {
                // portal data-direktur
                Route::group(['prefix' => 'data-direktur'], function () {
                    Route::get('/', [DataDirekturController::class, 'index'])->name('prt.apps.data.direktur.index');
                    Route::get('/create', [DataDirekturController::class, 'create'])->name('prt.apps.data.direktur.create');
                    Route::post('/create', [DataDirekturController::class, 'store'])->name('prt.apps.data.direktur.store');
                    Route::get('/edit/{uuid}', [DataDirekturController::class, 'edit'])->name('prt.apps.data.direktur.edit');
                    Route::put('/edit/{uuid}', [DataDirekturController::class, 'update'])->name('prt.apps.data.direktur.update');
                    Route::post('/delete', [DataDirekturController::class, 'destroy'])->name('prt.apps.data.direktur.destroy');
                    Route::post('/status', [DataDirekturController::class, 'status'])->name('prt.apps.data.direktur.status');
                });
                // portal halaman
                Route::group(['prefix' => 'halaman/{tags}'], function () {
                    Route::get('/', [HalamanController::class, 'index'])->name('prt.apps.page.index');
                    Route::get('/create', [HalamanController::class, 'create'])->name('prt.apps.page.create');
                    Route::post('/create', [HalamanController::class, 'store'])->name('prt.apps.page.store');
                    Route::get('/edit/{uuid}', [HalamanController::class, 'edit'])->name('prt.apps.page.edit');
                    Route::put('/edit/{uuid}', [HalamanController::class, 'update'])->name('prt.apps.page.update');
                    Route::post('/delete', [HalamanController::class, 'destroy'])->name('prt.apps.page.destroy');
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
                });
            });
            // middleware: Editor
            Route::group(['middleware' => ['Editor']], function () {
                // portal testimoni
                // Route::group(['prefix' => 'testimoni'], function () {
                //     Route::get('/', [TestimoniController::class, 'index'])->name('prt.apps.testimoni.index');
                //     Route::get('/create', [TestimoniController::class, 'create'])->name('prt.apps.testimoni.create');
                //     Route::post('/create', [TestimoniController::class, 'store'])->name('prt.apps.testimoni.store');
                //     Route::get('/edit/{uuid}', [TestimoniController::class, 'edit'])->name('prt.apps.testimoni.edit');
                //     Route::put('/edit/{uuid}', [TestimoniController::class, 'update'])->name('prt.apps.testimoni.update');
                //     Route::post('/delete', [TestimoniController::class, 'destroy'])->name('prt.apps.testimoni.destroy');
                //     Route::post('/status', [TestimoniController::class, 'status'])->name('prt.apps.testimoni.status');
                // });
                // portal running-text
                Route::group(['prefix' => 'running-text'], function () {
                    Route::get('/', [RunningTextController::class, 'index'])->name('prt.apps.runningtext.index');
                    Route::get('/create', [RunningTextController::class, 'create'])->name('prt.apps.runningtext.create');
                    Route::post('/create', [RunningTextController::class, 'store'])->name('prt.apps.runningtext.store');
                    Route::get('/edit/{uuid}', [RunningTextController::class, 'edit'])->name('prt.apps.runningtext.edit');
                    Route::put('/edit/{uuid}', [RunningTextController::class, 'update'])->name('prt.apps.runningtext.update');
                    Route::post('/delete', [RunningTextController::class, 'destroy'])->name('prt.apps.runningtext.destroy');
                    Route::post('/status', [RunningTextController::class, 'status'])->name('prt.apps.runningtext.status');
                });
                // portal greeting
                Route::group(['prefix' => 'greeting'], function () {
                    Route::get('/', [GreetingController::class, 'index'])->name('prt.apps.greeting.index');
                    Route::get('/create', [GreetingController::class, 'create'])->name('prt.apps.greeting.create');
                    Route::post('/create', [GreetingController::class, 'store'])->name('prt.apps.greeting.store');
                    Route::get('/edit/{uuid}', [GreetingController::class, 'edit'])->name('prt.apps.greeting.edit');
                    Route::put('/edit/{uuid}', [GreetingController::class, 'update'])->name('prt.apps.greeting.update');
                    Route::post('/delete', [GreetingController::class, 'destroy'])->name('prt.apps.greeting.destroy');
                    Route::post('/status', [GreetingController::class, 'status'])->name('prt.apps.greeting.status');
                });
                // portal faq
                Route::group(['prefix' => 'faq'], function () {
                    Route::get('/', [FAQController::class, 'index'])->name('prt.apps.faq.index');
                    Route::get('/create', [FAQController::class, 'create'])->name('prt.apps.faq.create');
                    Route::post('/create', [FAQController::class, 'store'])->name('prt.apps.faq.store');
                    Route::get('/edit/{uuid}', [FAQController::class, 'edit'])->name('prt.apps.faq.edit');
                    Route::put('/edit/{uuid}', [FAQController::class, 'update'])->name('prt.apps.faq.update');
                    Route::post('/delete', [FAQController::class, 'destroy'])->name('prt.apps.faq.destroy');
                    Route::post('/status', [FAQController::class, 'status'])->name('prt.apps.faq.status');
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
                // portal infografis
                Route::group(['prefix' => 'infografis'], function () {
                    Route::get('/', [InfografisController::class, 'index'])->name('prt.apps.infografis.index');
                    Route::get('/create', [InfografisController::class, 'create'])->name('prt.apps.infografis.create');
                    Route::post('/create', [InfografisController::class, 'store'])->name('prt.apps.infografis.store');
                    Route::get('/edit/{uuid}', [InfografisController::class, 'edit'])->name('prt.apps.infografis.edit');
                    Route::put('/edit/{uuid}', [InfografisController::class, 'update'])->name('prt.apps.infografis.update');
                    Route::post('/delete', [InfografisController::class, 'destroy'])->name('prt.apps.infografis.destroy');
                    Route::post('/status', [InfografisController::class, 'status'])->name('prt.apps.infografis.status');
                });
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

            /*
        |--------------------------------------------------------------------------
        | MENU KONTEN DIGITAL
        |--------------------------------------------------------------------------
        */
            // middleware: Penulis
            Route::group(['middleware' => ['Penulis']], function () {
                // portal ebook
                Route::group(['prefix' => 'ebook'], function () {
                    Route::get('/', [EbookController::class, 'index'])->name('prt.apps.ebook.index');
                    Route::get('/create', [EbookController::class, 'create'])->name('prt.apps.ebook.create');
                    Route::post('/create', [EbookController::class, 'store'])->name('prt.apps.ebook.store');
                    Route::get('/edit/{uuid}', [EbookController::class, 'edit'])->name('prt.apps.ebook.edit');
                    Route::put('/edit/{uuid}', [EbookController::class, 'update'])->name('prt.apps.ebook.update');
                    Route::post('/delete', [EbookController::class, 'destroy'])->name('prt.apps.ebook.destroy');
                });
                // portal emagazine
                Route::group(['prefix' => 'emagazine'], function () {
                    Route::get('/', [EmagazineController::class, 'index'])->name('prt.apps.emagazine.index');
                    Route::get('/create', [EmagazineController::class, 'create'])->name('prt.apps.emagazine.create');
                    Route::post('/create', [EmagazineController::class, 'store'])->name('prt.apps.emagazine.store');
                    Route::get('/edit/{uuid}', [EmagazineController::class, 'edit'])->name('prt.apps.emagazine.edit');
                    Route::put('/edit/{uuid}', [EmagazineController::class, 'update'])->name('prt.apps.emagazine.update');
                    Route::post('/delete', [EmagazineController::class, 'destroy'])->name('prt.apps.emagazine.destroy');
                });
                // portal esertifikat
                Route::group(['prefix' => 'esertifikat'], function () {
                    Route::get('/', [EsertifikatController::class, 'index'])->name('prt.apps.esertifikat.index');
                    Route::get('/create', [EsertifikatController::class, 'create'])->name('prt.apps.esertifikat.create');
                    Route::get('/edit/{uuid}', [EsertifikatController::class, 'edit'])->name('prt.apps.esertifikat.edit');
                    Route::put('/edit/{uuid}', [EsertifikatController::class, 'update'])->name('prt.apps.esertifikat.update');
                    Route::post('/delete', [EsertifikatController::class, 'destroy'])->name('prt.apps.esertifikat.destroy');
                    Route::post('/upload', [EsertifikatController::class, 'upload'])->name('prt.apps.esertifikat.upload');
                });
            });

            /*
        |--------------------------------------------------------------------------
        | MENU KONTEN EVENT
        |--------------------------------------------------------------------------
        */
            // middleware: Editor
            Route::group(['middleware' => ['Editor']], function () {
                // portal duta-sma
                Route::group(['prefix' => 'duta-sma'], function () {
                    Route::get('/', [DutaSMAController::class, 'index'])->name('prt.apps.dutasma.index');
                    Route::get('/create', [DutaSMAController::class, 'create'])->name('prt.apps.dutasma.create');
                    Route::post('/create', [DutaSMAController::class, 'store'])->name('prt.apps.dutasma.store');
                    Route::get('/edit/{uuid}', [DutaSMAController::class, 'edit'])->name('prt.apps.dutasma.edit');
                    Route::put('/edit/{uuid}', [DutaSMAController::class, 'update'])->name('prt.apps.dutasma.update');
                    Route::post('/delete', [DutaSMAController::class, 'destroy'])->name('prt.apps.dutasma.destroy');
                    Route::post('/status', [DutaSMAController::class, 'status'])->name('prt.apps.dutasma.status');
                });
                // portal tanos
                Route::group(['prefix' => 'tanos/{tags}'], function () {
                    Route::get('/', [TanosController::class, 'index'])->name('prt.apps.tanos.index');
                    Route::get('/create', [TanosController::class, 'create'])->name('prt.apps.tanos.create');
                    Route::get('/edit/{uuid}', [TanosController::class, 'edit'])->name('prt.apps.tanos.edit');
                    Route::put('/edit/{uuid}', [TanosController::class, 'update'])->name('prt.apps.tanos.update');
                    Route::post('/delete', [TanosController::class, 'destroy'])->name('prt.apps.tanos.destroy');
                    Route::post('/status', [TanosController::class, 'status'])->name('prt.apps.tanos.status');
                });
            });

            /*
        |--------------------------------------------------------------------------
        | MENU HELPDESK
        |--------------------------------------------------------------------------
        */
            // middleware: Operator
            Route::group(['middleware' => ['Operator']], function () {
                // portal pesan
                Route::group(['prefix' => 'pesan'], function () {
                    Route::get('/', [KotakPesanController::class, 'index'])->name('prt.apps.kotak.pesan.index');
                    Route::get('/read/{uuid}', [KotakPesanController::class, 'edit'])->name('prt.apps.kotak.pesan.edit');
                    Route::put('/read/{uuid}', [KotakPesanController::class, 'update'])->name('prt.apps.kotak.pesan.update');
                    Route::post('/delete', [KotakPesanController::class, 'destroy'])->name('prt.apps.kotak.pesan.destroy');
                });
            });

            // portal statistik
            Route::group(['prefix' => 'statistik'], function () {
                // Route::get('/', [StatistikController::class, 'index'])->name('prt.apps.stat.index');
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
                // cetak
                Route::group(['prefix' => 'cetak'], function () {});
            });

            // AUTH
            Route::group(['prefix' => 'auth'], function () {
                Route::get('/logout', [AuthController::class, 'logout'])->name('prt.lgn.logout');
            });
        });
    });
});
