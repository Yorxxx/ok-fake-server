<?php

namespace App;

use App\Repositories\NexmoRepository;
use App\Repositories\SMSPubliRepository;
use Tests\BrowserKitTestCase;
use Tests\Unit\Repositories\NexmoMockClient;

class SMSPubliRepositoryTest extends BrowserKitTestCase
{

    /**
     * Should generate a valid request
     * @test
     */
    public function given_message_when_generateRequest_Then_GeneratesValidRequest() {

        $repository = new SMSPubliRepository();

        // Act
        $result = $repository->generateRequest("foo", "+34-123456789");

        // Assert
        self::assertNotNull($result);
        $json_a = json_decode($string = trim(preg_replace('/\s+/', ' ', $result)));
        self::assertNotNull($json_a->api_key);
        self::assertNotNull($json_a->messages);
        self::assertEquals(env('SMS_EMISOR_NAME', null), $json_a->messages[0]->from);
        self::assertEquals("+34123456789", $json_a->messages[0]->to);
        self::assertEquals("foo", $json_a->messages[0]->text);
    }
}
