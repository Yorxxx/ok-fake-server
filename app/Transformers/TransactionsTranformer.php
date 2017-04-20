<?php
namespace App\Transformers;

use App\Transaction;
use Faker\Provider\DateTime;
use League\Fractal\TransformerAbstract;

class TransactionsTranformer extends TransformerAbstract
{
    public function transform(Transaction $transaction)
    {

        return [
            'id'                    => $transaction->id,
            'agent_destination'     => [
                'id'                => $transaction->destination->id,
                'name'              => $transaction->destination->name,
                'phone'             => $transaction->destination->localPhone(),
                'prefix'            => $transaction->destination->prefix(),
                'account'           => $transaction->destination->account == null ? '' : $transaction->destination->account,
                'country'           => $transaction->destination->country,
                'sort_code'         => ''
            ],
            'agent_source'          => [
                'account'           => $transaction->source->number,
            ],
            'date_start'            => $transaction->date_start->getTimestamp()*1000,
            'date_end'              => $transaction->date_end->getTimestamp()*1000,
            'date_creation'         => $transaction->date_creation->getTimestamp()*1000,
            'amount_destination'    => (string)$transaction->amount_destination,
            'amount_estimated'      => (string)$transaction->amount_source,
            'state'                 => (int)$transaction->state,
            'concept'               => $transaction->concept,
            'currency_destination'  => $transaction->currency_destination,
            'amount_source'         => $transaction->amount_source,
            'currency_source'       => $transaction->currency_source
        ];
    }

    /**
     * Maps the expected input request values into Laravel expected models
     * Does not validate any data. You should validate the incoming data before mapping
     * @param $values array containing the request data
     * @return array with mappable data
     */
    public function mapFromRequest($values) {
        if ($values == null)
            return null;

        return [
            'concept'               => $values['concept'],
            'amount_source'         => $values['amount'],
            'amount_destination'    => $values['amount_estimated'],
            'currency_source'       => $values['currency_source'],
            'currency_destination'  => $values['currency_destination'],
            'state'                 => array_key_exists('state', $values) ? $values['state'] : 0,
            'frequency'             => array_key_exists('frequency', $values) ? $values['frequency'] : 1,
            'sms_custom_text'       => $values['concept'],
            'agent_destination'     => $values['agent_destination'],
            'user_id'               => array_key_exists('user_id', $values) ? $values['user_id'] : 0,
            'agent_source'          => array_key_exists('agent_source', $values) ? $values['agent_source'] : 0
        ];

    }
}