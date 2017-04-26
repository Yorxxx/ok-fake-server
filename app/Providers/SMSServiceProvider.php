<?php

namespace App\Providers;

use App\Repositories\NexmoRepository;
use App\Repositories\SMSRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Nexmo;

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
        /* codeIgnoreStart */
        // Provide our SMSRepositoryInterface
        $this->app->singleton(SMSRepositoryInterface::class, function ($app) {
            $key = env('SMS_API_KEY', null);
            $secret = env('SMS_API_SECRET', null);
            //$sid = 'ACa7d14424263a6813b8f58cbaa6ebd818';
            //$token = '9a6f42b147ea8f8641dcf0c0934ca0ec';
            //$client = new Client($sid, $token);
            $client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic($key, $secret));
            return new NexmoRepository($client);
        });
        /* codeIgnoreEnd */
    }
}
