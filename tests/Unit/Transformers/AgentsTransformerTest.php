<?php

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AgentsTransformerTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Given an agent, the transformer should map into expected output data
     */
    public function given_agent_when_transform_Then_ReturnsCorrectFormatedJsonData()
    {

        $transformer = new \App\Transformers\AgentsTranformer;

        $user = factory(App\User::class)->create();

        $agent = factory(App\Agent::class)->create([
            'account'   => 'foo',
            'owner'     => 0,
            'name'      => 'foo bar',
            'phone'     => '+45-123456789',
            'email'     => "foo@bar.com",
            'country'   => "ES",
            'user_id'   => $user->id
        ]);

        // Act
        $result = $transformer->transform($agent);

        // Assert
        self::assertNotNull($result);
        self::assertArrayHasKey('account', $result);
        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('owner', $result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('email', $result);
        self::assertArrayHasKey('country', $result);
        self::assertArrayHasKey('prefix', $result);
        self::assertArrayHasKey('phone', $result);
        self::assertArrayHasKey('user_id', $result);
        self::assertEquals($agent->id, $result['id']);
        self::assertEquals('foo', $result['account']);
        self::assertEquals(false, $result['owner']);
        self::assertEquals("foo bar", $result['name']);
        self::assertEquals("foo@bar.com", $result['email']);
        self::assertEquals("ES", $result['country']);
        self::assertEquals("+45", $result['prefix']);
        self::assertEquals("123456789", $result['phone']);
        self::assertEquals($user->id, $result['user_id']);
    }

    /**
     * @test
     * Passing null to the mapper should return null
     */
    public function given_nullRequest_When_mapFromRequest_Then_ReturnsNull() {

        // Arrange
        $transformer = new \App\Transformers\AgentsTranformer;

        // Act
        $result = $transformer->mapFromRequest(null);

        // Assert
        self::assertNull($result);
    }

    /**
     * @test
     * Passing null to the mapper should throw
     */
    public function given_input_When_mapFromRequest_Then_ReturnsMappableData() {

        // Arrange
        $transformer = new \App\Transformers\AgentsTranformer;

        // Act
        $result = $transformer->mapFromRequest([
            'owner'     => false,
            'name'      => 'Foo Bar',
            'phone'     => 665547878,
            'prefix'    => 34,
            'account'   => "ES1521002719380200073017",
            'email'     => '',
            'country'   => 'ES']);

        // Assert
        self::assertNotNull($result);
        self::assertEquals(false, $result['owner']);
        self::assertEquals("Foo Bar", $result['name']);
        self::assertEquals("+34-665547878", $result['phone']);
        self::assertEquals("ES1521002719380200073017", $result['account']);
        self::assertEquals("ES", $result['country']);
        self::assertArrayNotHasKey("email", $result);
    }

    /**
     * @test
     * If the input does not have an expected key, do not return an array with an empty or null value, removing the null value keys from the array
     */
    public function given_missingKeys_When_mapFromRequest_Then_ReturnsMappedDataWithoutNullValueKeys() {

        // Arrange
        $transformer = new \App\Transformers\AgentsTranformer;

        // Act
        $result = $transformer->mapFromRequest([
            'email'     => 'aa',
            'country'   => 'ES']);

        // Assert
        self::assertNotNull($result);
        self::assertArrayNotHasKey('name', $result);
        self::assertArrayNotHasKey('owner', $result);
        self::assertArrayNotHasKey('phone', $result);
        self::assertArrayNotHasKey('account', $result);
        self::assertEquals("ES", $result['country']);
        self::assertEquals("aa", $result['email']);
    }
}
