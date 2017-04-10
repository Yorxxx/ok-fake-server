<?php
namespace App\Transformers;

use App\Transaction;
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
                'account'           => $transaction->destination->account,
                'country'           => $transaction->destination->country,
                'sort_code'         => ''
            ],
            'agent_source'          => [
                'account'           =>  $transaction->source->account
            ],
            'date_start'            => $transaction->date_start,
            'date_end'              => $transaction->date_end,
            'date_creation'         => $transaction->date_creation,
            'amount_destination'    => (double)$transaction->amount_destination,
            'amount_estimated'      => (double)$transaction->amount_source,
            'state'                 => (int)$transaction->state,
            'concept'               => $transaction->concept,
            'currency_destination'  => $transaction->currency_destination,
            'amount_source'         => $transaction->amount_source,
            'currency_source'       => $transaction->currency_source
        ];
    }
}