<?php

namespace App\Providers;

use App\Models\Pegawai;
use App\Observers\PegawaiObserver;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        Pegawai::observe(PegawaiObserver::class);
    }
}
