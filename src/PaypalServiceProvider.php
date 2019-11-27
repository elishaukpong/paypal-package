<?php

namespace Drumzminister\Paypal;

use Illuminate\Support\ServiceProvider;

class PaypalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Paypal', Paypal::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
