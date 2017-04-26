<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 26/04/17
 * Time: 10:16
 */

namespace App\Repositories;


class NexmoRepository implements SMSRepositoryInterface
{

    protected $client;

    /**
     * NexmoRepository constructor.
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
        $this->client->message()->send([
            'to' => preg_replace("/[^0-9+]/", "", $destination ),
            'from' => env('SMS_EMISOR_NAME', 'Opencash'),
            'text' => $message
        ]);
    }
}