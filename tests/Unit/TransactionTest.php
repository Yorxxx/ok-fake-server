<?php

use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TransactionTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Given a transaction, should return destination agent
     */
    public function given_transaction_when_destination_Then_ReturnsDestinationAgent() {
        $user = factory(App\User::class)->create([]);

        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);

        $transaction = factory(App\Transaction::class)->create([
            'agent_destination' => $agent->id
        ]);

        // Act
        $result = $transaction->destination;

        // Assert
        self::assertNotNull($result);
        self::assertEquals($agent->id,$result->id);
    }

    /**
     * @test
     * Given a transaction, should return source agent
     */
    public function given_transaction_when_source_Then_ReturnsSourceAgent() {
        $user = factory(App\User::class)->create([]);

        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id,
            'account'   => 'foo'
        ]);

        $transaction = factory(App\Transaction::class)->create([
            'agent_source' => $agent->account
        ]);

        // Act
        $result = $transaction->source;

        // Assert
        self::assertNotNull($result);
        self::assertEquals($agent->id, $result->id);
    }
}
