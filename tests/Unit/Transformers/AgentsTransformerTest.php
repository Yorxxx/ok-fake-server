<?php

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AgentTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Given an agent, the transformer should map into expected output data
     */
    public function given_agent_when_transform_Then_ReturnsCorrectFormatedJsonData()
    {

        $transformer = new \App\Transformers\AgentsTranformer;

        $agent = factory(App\Agent::class)->create([
            'account'   => 'foo',
            'owner'     => 0,
            'name'      => 'foo bar',
            'phone'     => '+45-123456789',
            'email'     => "foo@bar.com",
            'country'   => "ES",
            'user_id'   => 1
        ]);

        // Act
        $result = $transformer->transform($agent);

        // Assert
        self::assertNotNull($result);
        self::assertArrayHasKey('account', $result);
        self::assertArrayHasKey('owner', $result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('email', $result);
        self::assertArrayHasKey('country', $result);
        self::assertArrayHasKey('prefix', $result);
        self::assertArrayHasKey('phone', $result);
        self::assertEquals($agent->id, $result['account']);
        self::assertEquals(false, $result['owner']);
        self::assertEquals("foo bar", $result['name']);
        self::assertEquals("foo@bar.com", $result['email']);
        self::assertEquals("ES", $result['country']);
        self::assertEquals("+45", $result['prefix']);
        self::assertEquals("123456789", $result['phone']);
    }
}
