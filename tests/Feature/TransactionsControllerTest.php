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

    /**
     * @test
     * Test: GET: /api/transactions
     */
    public function given_authorizedUserWithTransactions_When_getTransactions_Then_ReturnsUserTransactions() {

        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $source_agent = factory(App\Agent::class)->create([
            'user_id' => $user->id]);
        $source_agent->user()->associate($user);

        $dest_user = factory(App\User::class)->create([
            'name'  => 'John Doe',
            'document' => '44444444',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);
        $dest_agent = factory(App\Agent::class)->create(['user_id' => $dest_user->id]);
        $dest_agent->user()->associate($dest_user);

        $transaction = factory(App\Transaction::class)->create([
            'user_id' => $user->id,
            'agent_source' => $source_agent->id,
            'agent_destination' => $dest_agent->id
        ]);

        $this->get('/api/transactions', $this->headers($user))
            ->seeStatusCode(200)
            ->seeJsonStructure([
                "results" => [
                    '*' => [
                        'id', 'date_start', 'date_end', 'date_creation', 'amount_destination', 'amount_estimated',
                        'state', 'concept', 'currency_destination', 'amount_source', 'currency_source'
                    ]
                ]
            ]);
    }
}
