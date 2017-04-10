<?php
namespace App\Transformers;

use App\Account;
use App\Agent;
use App\Setting;
use App\Transaction;
use League\Fractal\TransformerAbstract;

class TransactionsTranformer extends TransformerAbstract
{
    public function transform(Transaction $transaction)
    {

        return [
            'id'                    => $transaction->id,
            'agent_destination'     => [
                'id'        => $transaction->destination->id,
                'name'      => $transaction->destination->name,
                'phone'     => $transaction->destination->localPhone(),
                'prefix'    => $transaction->destination->prefix()
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
//            'account'           => $agent->id,
//            'owner'             => (bool)$agent->owner,
//            'name'              => $agent->name,
//            'email'             => $agent->email,
//            'country'           => $agent->country,
//            'prefix'            => $prefix,
//            'phone'            => $phone
        ];
    }

    /**
     * $table->increments('id');
    $table->string('concept');
    $table->decimal('amount_source');
    $table->decimal('amount_destination');
    $table->string('currency_source');
    $table->string('currency_destination');
    $table->integer('state');
    $table->integer('frequency');
    $table->string('sms_custom_text')->nullable();
    $table->string('ticket_otp')->nullable();
    $table->integer('agent_destination');
    $table->integer('agent_source');
    $table->integer('user_id');
    $table->timestamp('date_creation')->nullable();
    $table->timestamp('date_start')->nullable();
    $table->timestamp('date_end')->nullable();
    $table->timestamps();
     */
}