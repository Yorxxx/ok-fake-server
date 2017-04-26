<?php

namespace App\Providers;

use App\Repositories\NexmoRepository;
use App\Repositories\SMSPubliRepository;
use App\Repositories\SMSRepositoryInterface;
use App\Repositories\TwilioRepository;
use Illuminate\Support\ServiceProvider;
use Nexmo;
use Tests\Unit\Repositories\SMSMockRepository;
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
            $provider = env('SMS_PROVIDER');
            if ($this->app->runningUnitTests()) {
                $provider = config('SMS_PROVIDER');
            }

            $key = env('SMS_API_KEY', "foo");
            $secret = env('SMS_API_SECRET', "foosecret");
            if (strcmp($provider, 'NEXMO') == 0) {
                $client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic($key, $secret));
                return new NexmoRepository($client);
            }
            else if (strcmp($provider, 'TWILIO') == 0) {
                $client = new Client($key, $secret);
                return new TwilioRepository($client);
            }
            else if (strcmp($provider, 'SMS_PUBLI') == 0) {
                return new SMSPubliRepository();
            }
            return new SMSMockRepository();
        });
    }
}
