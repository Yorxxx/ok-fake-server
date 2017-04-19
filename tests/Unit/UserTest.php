<?php

use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Given a user, should retrieve its agents
     */
    public function given_user_when_agents_Then_ReturnsUserAgents() {
        $user = factory(App\User::class)->create(['name'  => 'John Doe']);

        $agent1 = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);
        $agent2 = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $result = $user->agents;

        // Assert
        self::assertNotNull($result);
        self::assertCount(2, $result);
        self::assertEquals($agent1->id, $result[0]->id);
        self::assertEquals($agent2->id, $result[1]->id);
    }

    /**
     * @test
     * Given a user with transactions, should be able to retrieve them (received and emitted
     */
    public function given_userWithTransaction_When_transactions_Then_ReturnsTransaction() {

        $user = factory(App\User::class)->create([]);
        factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);

        $transaction = factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $account->id
        ]);
        $transaction2 = factory(App\Transaction::class)->create([
            'user_id' => $user->id,
            'account_source'    => $account->id
        ]);

        // Act
        $result = $user->transactions;

        // Assert
        self::assertNotNull($result);
        self::assertCount(2, $result);
        self::assertEquals($transaction->id, $result[0]->id);
        self::assertEquals($transaction2->id, $result[1]->id);
    }

    /**
     * @test
     * Given a user with accounts, should be able to return them
     */
    public function given_userAccounts_When_Accounts_Then_ReturnsUserAccounts() {

        $user = factory(App\User::class)->create([]);
        $account1 = factory(App\Account::class)->create([
            'user_id'       => $user->id
        ]);
        $account2 = factory(App\Account::class)->create([
            'user_id'       => $user->id
        ]);

        // Act
        $result = $user->accounts;

        // Assert
        self::assertNotNull($result);
        self::assertCount(2, $result);
        self::assertEquals($user->id, $result[0]->user_id);
        self::assertEquals($user->id, $result[1]->user_id);
    }
}
