<?php

namespace Tests\Unit\Repositories;

/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 25/04/17
 * Time: 11:46
 */
class TwilioMockClient
{
    public $messagesCalled = false;
    public $messages;

    /**
     * TwilioMockClient constructor.
     */
    public function __construct()
    {
        $this->messages = new MessageClient();
    }
}

class MessageClient {

    public $createCalled = false;
    public $phoneValue;
    public $payload;

    public function create($phone, $payload) {
        $this->createCalled = true;
        $this->phoneValue = $phone;
        $this->payload = $payload;
    }
}