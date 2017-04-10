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
        self::assertEquals($user->id, $result->id);
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

    /**
     * @test
     * Given an agent with phone and prefix, should be able to return phone value only
     */
    public function given_agentWithPhone_When_localPhone_Then_ReturnsPhoneWithoutPreffix() {

        $user = factory(App\User::class)->create([]);

        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id,
            'phone'     => '+34-123456789'
        ]);

        // Act
        $result = $agent->localPhone();

        // Assert
        self::assertNotNull($result);
        self::assertSame("123456789", $result);
    }

    /**
     * @test
     * Given an agent with phone and prefix, should be able to return phone value only
     */
    public function given_agentWithPhone_When_prefix_Then_ReturnsPhonePrefix() {

        $user = factory(App\User::class)->create([]);

        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id,
            'phone'     => '+34-123456789'
        ]);

        // Act
        $result = $agent->prefix();

        // Assert
        self::assertNotNull($result);
        self::assertSame("34", $result);
        self::assertStringStartsNotWith("+", $result);
    }

    /**
     * @test
     * Given an agent without phone, should return null when is requested about the phone prefix
     */
    public function given_agentWithoutPhone_When_prefix_Then_ReturnsNull() {

        $user = factory(App\User::class)->create([]);

        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id,
            'phone'     => null
        ]);

        // Act
        $result = $agent->prefix();

        // Assert
        self::assertNull($result);
    }

    /**
     * @test
     * Given an agent without phone, should return null when is requested about the local phone
     */
    public function given_agentWithoutPhone_When_localPhone_Then_ReturnsNull() {

        $user = factory(App\User::class)->create([]);

        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id,
            'phone'     => null
        ]);

        // Act
        $result = $agent->localPhone();

        // Assert
        self::assertNull($result);
    }
}
