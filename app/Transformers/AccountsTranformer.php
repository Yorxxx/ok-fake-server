<?php
namespace App\Transformers;

use App\Account;
use League\Fractal\TransformerAbstract;

class AccountsTranformer extends TransformerAbstract
{
    public function transform(Account $account)
    {
        return [
            'id'        => (int) $account->id,
            'number'    => $account->number,
            'linked'    => (bool)$account->linked,
            'currency'  => $account->currency,
            'amount'    => (double)$account->amount,
            'user_id'   => (int)$account->user_id,
            'alias'     => $account->alias
        ];
    }
}