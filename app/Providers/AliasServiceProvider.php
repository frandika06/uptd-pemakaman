<?php

namespace App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class AliasServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Helper', \App\Helpers\Helper::class);
        $loader->alias('Captcha', \Mews\Captcha\Facades\Captcha::class);
        $loader->alias('Alert', \RealRashid\SweetAlert\Facades\Alert::class);
        $loader->alias('DataTables', \Yajra\DataTables\Facades\DataTables::class);
        $loader->alias('Excel', \Maatwebsite\Excel\Facades\Excel::class);
        $loader->alias('Socialite', Laravel\Socialite\Facades\Socialite::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}