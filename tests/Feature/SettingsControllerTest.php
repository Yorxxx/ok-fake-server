<?php

namespace Tests\Feature;

use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsControllerTest extends BrowserKitTestCase
{
    /**
     * @test
     * Test: GET: /api/settings
     */
    public function given_noAuthorization_When_getSettings_Then_Returns401()
    {
        $this->get('/api/settings')->seeStatusCode(401);
    }
}
