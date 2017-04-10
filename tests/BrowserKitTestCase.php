<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use Illuminate\Contracts\Console\Kernel;
use Laravel\BrowserKitTesting\TestCase;
use JWTAuth;

abstract class BrowserKitTestCase extends TestCase
{
    /**
     * The base URL of the application.
     *
     * @var string
     */
    public $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {

        putenv('DB_CONNECTION=sqlite_testing');

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Return request headers needed to interact with the API.
     *
     * @return Array array of headers.
     */
    protected function headers($user = null, $pass = "foo")
    {
        $headers = ['Accept' => 'application/json'];

        if (!is_null($user)) {
            $credentials = [
                'document' => $user->document,
                'password' => $pass,
                'doctype' => $user->doctype];
            $token = JWTAuth::fromUser($user);
            //$token = JWTAuth::fromUser($user);
            JWTAuth::setToken($token);
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $headers;
    }
}