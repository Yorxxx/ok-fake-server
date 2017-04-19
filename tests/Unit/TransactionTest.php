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
        $account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);

        $transaction = factory(App\Transaction::class)->create([
            'account_source'    => $account->id,
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
     * Given a transaction, should return source account
     */
    public function given_transaction_when_source_Then_ReturnsSourceAccount() {
        $user = factory(App\User::class)->create([]);

        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);
        $account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);

        $transaction = factory(App\Transaction::class)->create([
            'account_source'    => $account->id,
            'agent_destination' => $agent->id
        ]);

        // Act
        $result = $transaction->source;

        // Assert
        self::assertNotNull($result);
        self::assertEquals($account->id, $result->id);
    }
}
