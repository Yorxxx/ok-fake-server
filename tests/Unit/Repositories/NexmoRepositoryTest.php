<?php

namespace App;

use App\Repositories\NexmoRepository;
use Tests\BrowserKitTestCase;
use Tests\Unit\Repositories\NexmoMockClient;

class NexmoRepositoryTest extends BrowserKitTestCase
{

    /**
     * Should send the specified message to the specified destination.
     * @test
     */
    public function given_message_when_send_Then_SendsMessageToRequestedDestination() {

        $client = new NexmoMockClient;
        $repository = new NexmoRepository($client);

        // Act
        $repository->send("foo message", "+34-123456789");

        // Assert
        self::assertNotNull($client->message->sendCalled);
        self::assertEquals($client->message->payload['text'], "foo message");
        self::assertEquals($client->message->payload['to'], "34123456789");
        self::assertEquals($client->message->payload['from'], env('SMS_EMISOR_NAME', 'Opencash'));
    }
}
