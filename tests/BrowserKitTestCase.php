<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use Illuminate\Contracts\Console\Kernel;
use Laravel\BrowserKitTesting\TestCase;

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
}