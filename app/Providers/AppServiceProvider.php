<?php

namespace App\Providers;
use Carbon\Carbon;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      $locale = $this->app->getLocale();
      // setlocale(LC_TIME, $locale."_".strtoupper($locale));
      Carbon::setLocale($this->app->getLocale());
    }
}
