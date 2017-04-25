<?php

namespace App\Repositories;


interface SMSRepositoryInterface {

    /**
     * Sends a message to the specified destination
     * @param $message string the message to send
     * @param $destination string the destination (ie phone number)
     */
    public function send($message, $destination);
}