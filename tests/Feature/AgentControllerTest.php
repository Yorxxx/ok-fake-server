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
            ->seeJsonStructure([
                "results" => [
                    '*' => ['account', 'country', 'email', 'name', 'owner', 'phone', 'prefix']
                ]
            ]);
    }

    /**
     * @test
     * POST /api/agents
     * Adding new agents when not authorized should be forbidden
     */
    public function given_unauthorizedUser_When_AddAgent_Then_Returns401() {

        $this->post('/api/agents', [
            'owner'     => false,
            'name'      => 'Foo Bar',
            'phone'     => 665547878,
            'prefix'    => 34,
            'account'   => "ES1521002719380200073017",
            'email'     => '',
            'country'   => 'ES'])
            ->seeStatusCode(401);
    }

    /**
     * @test
     * @POST('/api/agents')
     * Authorized users are allowed to add new agents
     */
    public function given_authorizedUser_When_AddAgent_Then_Returns202() {

        // Arrange
        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);


        // Act
        $result = $this->post('/api/agents', [
            'owner'     => false,
            'name'      => 'Foo Bar',
            'phone'     => 665547878,
            'prefix'    => 34,
            'account'   => "ES1521002719380200073017",
            'email'     => '',
            'country'   => 'ES'], $this->headers($user));

        // Assert
        $result->seeStatusCode(202);
    }
}
