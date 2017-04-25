<?php

namespace App;

use Tests\BrowserKitTestCase;
use App\Repositories\TwilioRepository;
use Tests\Unit\Repositories\TwilioMockClient;

class TwilioRepositoryTest extends BrowserKitTestCase
{

    /**
     * Should send the specified message to the specified destination.
     * @test
     */
    public function given_message_when_send_Then_SendsMessageToRequestedDestination() {

        $client = new TwilioMockClient;
        $repository = new TwilioRepository($client);

        // Act
        $repository->send("foo message", "foodestination");

        // Assert
        self::assertTrue($client->messages->createCalled);
        self::assertEquals("foodestination", $client->messages->phoneValue);
        self::assertNotNull($client->messages->payload);
        self::assertEquals($client->messages->payload['Body'], "foo message");
    }
}
