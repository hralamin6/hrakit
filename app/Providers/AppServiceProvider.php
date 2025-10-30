<?php

namespace App\Providers;

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
        Paginator::defaultView('pagination::default');

        Paginator::defaultSimpleView('pagination::simple-default');

      try {
        if (\Schema::hasTable('settings')) {
          config([
            'app.name' => setting('site_name', config('app.name')),
            'mail.from.address' => setting('site_email', config('mail.from.address')),
            'mail.from.name' => setting('site_name', config('mail.from.name')),
          ]);
        }
      } catch (\Exception $e) {
        // ignore if during install
      }
    }
}
