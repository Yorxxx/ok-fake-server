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
     * Trying to add an agent with missing required params (like account or name), should throw a bad request error
     */
    public function given_missingRequiredParams_When_store_Then_Returns400() {

        // Arrange
        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);


        // Act
        $result = $this->post('/api/agents', [
            'owner'     => false,
            'phone'     => 665547878,
            'prefix'    => 34,
            'email'     => '',
            'country'   => 'ES'], $this->headers($user));

        // Assert
        $result->seeStatusCode(400);
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
     * @POST('/api/accounts/by_number')
     * @Request({"account": "foo"})
     * Not logged in users should not be able to retrieve accounts info by number
     */
    public function given_unauthorizedUser_When_getAccountByNumber_Then_Returns400() {
        $this->post('/api/accounts/by_number', [])
            ->seeStatusCode(401);
    }

    /**
     * @test
     * @POST('/api/accounts/by_number')
     * @Request({"account": "foo"})
     * Invalid requests payload return a bad request code
     */
    public function given_invalidRequest_When_getAccountByNumber_Then_ReturnsBadRequest() {

        $user = factory(App\User::class)->create();
        $account = factory(App\Agent::class)->create([
            'account'           => "foo",
            'user_id'           => $user->id
        ]);

        $this->post('/api/accounts/by_number', ['foo'   => $account->number], $this->headers($user))
            ->seeStatusCode(400)
            ->seeText("Missing or invalid param: account");
    }

    /**
     * @test
     * @POST('/api/accounts/by_number')
     * @Request({"account": "foo"})
     * If the account does not exist for the given user, return 404
     */
    public function given_authorizedUserAskingForNonExistingAccount_When_getAccountByNumber_Then_Returns404() {

        // Arrange
        $currentuser = factory(App\User::class)->create();

        // Create an account for another user. We will request it
        $user = factory(App\User::class)->create();
        factory(App\Agent::class)->create([
            'account'           => "foo",
            'user_id'           => $user->id
        ]);

        $this->post('/api/accounts/by_number', ['account'   => "bla"], $this->headers($currentuser))
            ->seeStatusCode(404);
    }

    /**
     * @test
     * @POST('/api/accounts/by_number')
     * @Request({"account": "foo"})
     * Asking for an existing account, should return its info
     */
    public function given_existingAccount_When_getAccountByNumber_Then_ReturnsAccount() {

        // Arrange
        $user = factory(App\User::class)->create();
        factory(App\Agent::class)->create([
            'account'           => "foo",
            'user_id'           => $user->id
        ]);

        // Act
        $result = $this->post('/api/accounts/by_number', ['account'   => "foo"], $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                'id', 'account', 'owner', 'name', 'email', 'country', 'prefix', 'phone', 'user_id']);
    }
}
