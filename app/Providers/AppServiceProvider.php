<?php
namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            // Force HTTPS unless the host is IP Local
            $host = request()->getHost();
            if ($host === '10.35.4.60') {
                resolve(\Illuminate\Routing\UrlGenerator::class)->forceScheme('http');
            } else {
                URL::forceRootUrl(config('app.url'));
                // Pastikan https jika APP_URL diawali https://
                $isHttps = str_starts_with(strval(config('app.url')), 'https://');
                URL::forceScheme($isHttps ? 'https' : 'http');
            }
        }

        Schema::defaultStringLength(191);
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');
    }

}