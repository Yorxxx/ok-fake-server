<?php

namespace App\Providers;

use App\Repositories\SMSRepositoryInterface;
use App\Repositories\TwilioRepository;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;

class SMSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Provide our SMSRepositoryInterface
        $this->app->singleton(SMSRepositoryInterface::class, function ($app) {
            $sid = 'ACa7d14424263a6813b8f58cbaa6ebd818';
            $token = '9a6f42b147ea8f8641dcf0c0934ca0ec';
            $client = new Client($sid, $token);
            return new TwilioRepository($client);
        });
    }
}
