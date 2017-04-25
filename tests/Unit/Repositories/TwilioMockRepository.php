<?php

namespace Tests\Unit\Repositories;
use App\Repositories\SMSRepositoryInterface;


/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 25/04/17
 * Time: 11:46
 */
class TwilioMockRepository implements SMSRepositoryInterface
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