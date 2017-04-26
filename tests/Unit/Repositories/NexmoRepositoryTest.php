<?php

namespace App;

use App\Repositories\NexmoRepository;
use Tests\BrowserKitTestCase;
use Tests\Unit\Repositories\NexmoMockClient;

class TwilioRepositoryTest extends BrowserKitTestCase
{

    /**
     * Should send the specified message to the specified destination.
     * @test
     */
    public function given_message_when_send_Then_SendsMessageToRequestedDestination() {

        $client = new NexmoMockClient;
        $repository = new NexmoRepository($client);

        // Act
        $repository->send("foo message", "foodestination");

        // Assert
        self::assertNotNull($client->message->sendCalled);
        self::assertEquals($client->message->payload['text'], "foo message");
        self::assertEquals($client->message->payload['to'], "foodestination");
    }
}
