<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 25/04/17
 * Time: 10:34
 */

namespace App\Repositories;

class TwilioRepository implements SMSRepositoryInterface
{

    protected $client;

    /**
     * TwilioRepository constructor.
     * @param $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Sends a message to the specified destination
     * @param $message string the message to send
     * @param $destination string the destination (ie phone number)
     */
    public function send($message, $destination)
    {
        /*$this->client->messages->create("+34646547055", array(
            'From' => "+34988057321",
            'Body' => $message,
        ));*/
        $this->client->messages->create($destination, array(
            'From' => "+34988057321", // Twilio client phone number
            'Body' => $message,
        ));
    }
}