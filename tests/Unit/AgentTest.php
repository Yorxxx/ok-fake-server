<?php

use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AgentTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Given an agent, should retrieve related user
     */
    public function given_agent_when_user_Then_ReturnsUser() {
        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $agent1 = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $result = $agent1->user;

        // Assert
        self::assertNotNull($result);
        self::assertEquals($user->id,$result->id);
    }

    /**
     * @test
     * Given an agent with received transactions, should be able to retrieve those transactions that have been emitted to him.
     */
    public function given_agentWithTransactions_When_receivedTransactions_Then_ReturnsTransaction() {

        $user = factory(App\User::class)->create([]);

        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);
        $transaction = factory(App\Transaction::class)->create([
            'agent_destination' => $agent->id
        ]);

        // Act
        $result = $agent->receivedTransactions;

        // Assert
        self::assertNotNull($result);
        self::assertCount(1, $result);
        self::assertEquals($transaction->id, $result[0]->id);
    }
}
