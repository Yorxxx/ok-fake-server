<?php

use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TransactionsControllerTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Test: GET: /api/transactions
     */
    public function given_noAuthorization_When_getTransactions_Then_Returns401()
    {
        $this->get('/api/transactions')->seeStatusCode(401);
    }

    /**
     * @test
     * Test: GET: /api/transactions
     */
    public function given_authorizedUserWithoutTransactions_When_getTransactions_Then_ReturnsEmptyList() {

        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $this->get('/api/transactions', $this->headers($user))
            ->seeStatusCode(200)
            ->seeJson([
                "results" => []
            ]);
    }
}
