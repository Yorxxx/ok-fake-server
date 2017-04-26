<?php

namespace Tests\Unit\Repositories;

class NexmoMockClient
{
    public $messagesCalled = false;
    public $message;

    /**
     * TwilioMockClient constructor.
     */
    public function __construct()
    {
        $this->message = new MessageClient();
    }

    public function message() {
        return $this->message;
    }
}

class MessageClient {

    public $sendCalled = false;
    public $payload;

    public function send($payload) {
        $this->sendCalled = true;
        $this->payload = $payload;
    }
}