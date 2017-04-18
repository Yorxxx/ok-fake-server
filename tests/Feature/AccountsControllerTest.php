<?php

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AccountsControllerTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Test: GET: /api/accounts
     */
    public function given_noAuthorization_When_getAccounts_Then_Returns401()
    {
        $this->get('/api/accounts')->seeStatusCode(401);
    }

    /**
     * @test
     * Test: GET /api/accounts
     */
    public function given_authorizedUser_When_GetAccounts_Then_ReturnsUserAccounts() {

        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        factory(App\Account::class)->create([
            'user_id' => $user->id
        ]);

        $this->get('/api/accounts', $this->headers($user))
            ->seeJsonStructure([
                '*' => [
                    'number', 'linked', 'currency', 'amount', 'alias'
                ]
            ]);
    }

    /**
     * @test
     * Test: GET /api/accounts
     */
    public function given_authorizedUserWithoutAccounts_When_GetAccounts_Then_ReturnsEmpty() {

        $this->seed('AccountsTableSeeder');

        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'email' => 'foo@bar.com',
            'password' => bcrypt('foo')]);


        $this->get('/api/accounts', $this->headers($user))
            ->seeJsonStructure([]);
    }

    /**
     * @test
     * @POST('/api/accounts/{id}/link')
     * Unauthorized users are not allowed to link accounts
     */
    public function given_unauthorizedUser_When_Link_Then_Returns400() {

        $this->post('/api/accounts/535/link', [])
            ->seeStatusCode(401);
    }

    /**
     * @test
     * @POST('/api/accounts/{id}/link')
     * Trying to link external accounts is forbidden
     */
    public function given_externalUser_When_Link_Then_Returns403() {

        // Arrange
        $currentuser = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $user = factory(\App\User::class)->create();

        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $this->post('/api/accounts/' . $account->id .'/link', [], $this->headers($currentuser))
            ->seeStatusCode(403)
            ->seeText("Cannot link an account that does not belongs to you");
    }

    /**
     * @test
     * @POST('/api/accounts/{id}/link')
     * Users should be able to link their accounts
     */
    public function given_currentUserAccount_When_Link_Then_Returns202() {
        // Arrange
        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id,
            'linked'    => 0
        ]);

        // Act
        $this->post('/api/accounts/' . $account->id . '/link', [], $this->headers($user))
            ->seeStatusCode(202);

        $account = \App\Account::where('user_id', $user->id)->first();

        self::assertNotNull($account);
        self::assertTrue((bool)$account->linked);
    }

    /**
     * @test
     * @POST('/api/accounts/{id}/unlink')
     * Unauthorized users are not allowed to unlink accounts
     */
    public function given_unauthorizedUser_When_Unlink_Then_Returns400() {

        $this->post('/api/accounts/535/unlink', [])
            ->seeStatusCode(401);
    }

    /**
     * @test
     * @POST('/api/accounts/{id}/unlink')
     * Trying to unlink external accounts is forbidden
     */
    public function given_externalUser_When_Unlink_Then_Returns403() {

        // Arrange
        $currentuser = factory(App\User::class)->create();

        $user = factory(\App\User::class)->create();

        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $this->post('/api/accounts/' . $account->id .'/unlink', [], $this->headers($currentuser))
            ->seeStatusCode(403)
            ->seeText("Cannot unlink an account that does not belongs to you");
    }

    /**
     * @test
     * @POST('/api/accounts/{id}/unlink')
     * Users should be able to unlink their accounts
     */
    public function given_currentUserAccount_When_Unlink_Then_Returns202() {
        // Arrange
        $user = factory(App\User::class)->create();

        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id,
            'linked'    => 1
        ]);

        // Act
        $this->post('/api/accounts/' . $account->id . '/unlink', [], $this->headers($user))
            ->seeStatusCode(202);

        $account = \App\Account::where('user_id', $user->id)->first();

        self::assertNotNull($account);
        self::assertFalse((bool)$account->linked);
    }
}
