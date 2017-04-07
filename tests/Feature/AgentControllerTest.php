<?php

namespace Tests\Feature;

use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AgentControllerTest extends BrowserKitTestCase
{

    use DatabaseMigrations;

    /**
     * @test
     * Test: GET: /api/agents
     */
    public function given_noAuthorization_When_getAgents_Then_Returns401()
    {
        $this->get('/api/agents')->seeStatusCode(401);
    }
}
