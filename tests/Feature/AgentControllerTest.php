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
    public function given_unauthorizedUser_When_store_Then_Returns401() {

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
    public function given_authorizedUser_When_store_Then_Returns202() {

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
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                'owner', 'name', 'phone', 'prefix', 'account', 'email', 'country', 'user_id', 'id'
            ]);
    }

    /**
     * @test
     * @POST('/api/agents)
     * Trying to add an agent with missing required params "name", should throw a bad request error
     */
    public function given_missingNameParams_When_store_Then_Returns400() {

        // Arrange
        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);


        // Act
        $result = $this->post('/api/agents', [
            'account'   => 'foo',
            'owner'     => false,
            'phone'     => 665547878,
            'prefix'    => 34,
            'email'     => '',
            'country'   => 'ES'], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("The name field is required.");
    }

    /**
     * @test
     * @POST('/api/agents)
     * Trying to add an agent with missing params "account", is allowed
     */
    public function given_missingAccountParam_When_store_Then_Returns400() {

        // Arrange
        $user = factory(App\User::class)->create();

        // Act
        $result = $this->post('/api/agents', [
            'name'   => 'foo',
            'owner'     => false,
            'phone'     => 665547878,
            'prefix'    => 34,
            'email'     => '',
            'country'   => 'ES'], $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                'owner', 'name', 'phone', 'prefix', 'account', 'email', 'country', 'user_id', 'id'
            ]);
    }

    /**
     * @test
     * @POST('/api/agents)
     * Trying to add an agent with missing required params "phone", should throw a bad request error
     */
    public function given_missingPhoneParam_When_store_Then_Returns400() {

        // Arrange
        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);


        // Act
        $result = $this->post('/api/agents', [
            'name'   => 'foo',
            'account'   => 'foo',
            'owner'     => false,
            'prefix'    => 34,
            'email'     => '',
            'country'   => 'ES'], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("The phone field is required.");
    }

    /**
     * @test
     * @POST('/api/agents')
     * If a mapping error, then return 500
     */
    public function given_mapError_When_store_Then_Returns500() {

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
            'account'   => "ES1521002719380200073017",
            'email'     => '',
            'country'   => 'ES'], $this->headers($user));

        // Assert
        $result->seeStatusCode(500);
    }

    /**
     * @test
     * @PUT('/api/agents/{id}')
     * Updating new agents when not authorized should be forbidden
     */
    public function given_unauthorizedUser_When_update_Then_Returns401() {

        $this->put('/api/agents/50', [
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
     * @PUT('/api/agents/{id}')
     * If the request agent to be updated does not exist, return 404
     */
    public function given_notFoundAgent_When_update_Then_Returns404() {

        $user = factory(\App\User::class)->create();

        // Act
        $result = $this->put('/api/agents/50', [
            'owner'     => false,
            'name'      => 'Foo Bar',
            'phone'     => 665547878,
            'prefix'    => 34,
            'account'   => "ES1521002719380200073017",
            'email'     => '',
            'country'   => 'ES'], $this->headers($user));

        // Assert
        $result->seeStatusCode(404);
    }

    /**
     * @test
     * @PUT('/api/agents/{id}')
     * Only the user associated to the specified agent is allowed to update the entity
     */
    public function given_agentNotAssociatedWithCurrentUser_When_update_Then_ReturnsForbidden() {

        // Arrange
        $user = factory(App\User::class)->create();
        $other_user = factory(App\User::class)->create();

        $agent = factory(\App\Agent::class)->create([
            'user_id'       => $other_user->id
        ]);

        // Act
        $result = $this->put('/api/agents/' . $agent->id, ['name'      => 'Foo Bar',], $this->headers($user));

        // Assert
        $result->seeStatusCode(403);
    }

    /**
     * @test
     * @PUT('/api/agents/{id}')
     * Allowed users can update their agents data
     */
    public function given_validAgent_When_update_Then_UpdatesAgent() {

        // Arrange
        $user = factory(App\User::class)->create();

        $agent = factory(\App\Agent::class)->create([
            'user_id'       => $user->id,
            'name'          => 'Foo',
            'account'       => '',
            'country'       => 'UK',
            'updated_at'    => \Carbon\Carbon::now()->subHour(1)
        ]);

        // Act
        $result = $this->put('/api/agents/' . $agent->id,
            [
                'name'      => 'Foo Bar',
                'account'   => 'Foo account',
                'country'   => 'ES'
            ], $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                'owner', 'name', 'phone', 'prefix', 'account', 'email', 'country', 'user_id', 'id'
            ]);

        $updated_agent = \App\Agent::where('id', $agent->id)->first();
        self::assertNotNull($updated_agent);
        self::assertEquals('Foo Bar', $updated_agent->name);
        self::assertEquals('Foo account', $updated_agent->account);
        self::assertEquals('ES', $updated_agent->country);
        self::assertEquals($agent->id, $updated_agent->id);
        self::assertEquals($agent->email, $updated_agent->email);
        self::assertEquals($agent->phone, $updated_agent->phone);
        self::assertEquals($agent->owner, $updated_agent->owner);
        self::assertTrue($updated_agent->updated_at->gt($agent->updated_at));
    }
}
