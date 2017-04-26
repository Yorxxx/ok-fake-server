<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 26/04/17
 * Time: 12:41
 */

namespace App\Repositories;


class SMSPubliRepository implements SMSRepositoryInterface
{
    /**
     * Sends a message to the specified destination
     * @param $message string the message to send
     * @param $destination string the destination (ie phone number)
     */
    public function send($message, $destination)
    {
        $headers = array('Content-Type: application/json');

        $ch = curl_init('https://api.gateway360.com/api/3.0/sms/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->generateRequest($message, $destination));

        curl_exec($ch);

        curl_close($ch);
    }

    /**
     * Generates a valid JSON request as required by provider
     * @param $message string the message to send
     * @param $destination string the destinatin of the SMS to send
     * @return string a json encoded string
     */
    public function generateRequest($message, $destination) {

        $from = env('SMS_EMISOR_NAME', 'Opencash');
        $to = preg_replace("/[^0-9+]/", "", $destination );
        $key = env('SMS_API_KEY');

        $request = '{
            "api_key":'. '"' . $key . '",
            "messages":[
                {
                    "from": ' . '"' . $from . '",
                    "to": ' . '"' . $to . '",
                    "text": ' . '"' . $message . '"
                }
            ]
        }';
        return $request;
    }
}