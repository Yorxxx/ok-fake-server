<?php

namespace Tests\Unit\Repositories;
use App\Repositories\SMSRepositoryInterface;


/**
 * A mock implementation of SMSRepositoryInterface for testing
 * Created by PhpStorm.
 * User: jorge
 * Date: 25/04/17
 * Time: 11:46
 */
class SMSMockRepository implements SMSRepositoryInterface
{
    public $sendCalled = false;
    public $requestedMessage = null;
    public $requestedDestination = null;

    public function send($message, $destination)
    {
        $this->sendCalled = true;
        $this->requestedMessage = $message;
        $this->requestedDestination = $destination;
    }

}