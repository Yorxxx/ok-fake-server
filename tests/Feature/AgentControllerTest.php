<?php

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

    /**
     * @test
     * Test: GET: /api/agents
     */
    public function given_authorizedUserWithoutAgents_When_getAgents_Then_ReturnsEmptyList() {

        $this->seed('UsersTableSeeder');
        $this->seed('AgentsTableSeeder');

        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $this->get('/api/agents', $this->headers($user))
            ->seeStatusCode(200)
            ->seeJson([
                "results" => []
            ]);
    }

    /**
     * @test
     * Test: GET: /api/agents
     */
    public function given_authorizedUserWithAgents_When_getAgents_Then_ReturnsAgentsList() {

        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $agent1 = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);
        $agent1->user()->associate($user);
        $agent2 = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);
        $agent2->user()->associate($user);

        // Act
        $this->get('/api/agents', $this->headers($user))
            ->seeStatusCode(200)
//            ->seeJsonEquals([
//                'results' => [
//                    '0' => [
//                        'account'   => $agent1->id,
//                        'country'   => $agent1->country,
//                        'email'     => $agent1->email,
//                        'name'      => $agent1->name,
//                        'owner'     => false,
//                        'phone'     => $agent1->phone
//                    ]
//                ]
//            ]);
            ->seeJsonStructure([
                "results" => [
                    '*' => ['account', 'country', 'email', 'name', 'owner', 'phone', 'prefix']
                ]
            ]);
    }

}
