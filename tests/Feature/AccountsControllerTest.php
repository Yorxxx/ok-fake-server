<?php

use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
            'email' => 'foo@bar.com',
            'password' => bcrypt('foo')]);

        $this->get('/api/accounts', $this->headers($user))
            ->seeStatusCode(201);
    }
}
