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
            'linked'    => (bool)$account->linked,//ucfirst($fruit->color),
            'currency'  => $account->currency,
            'amount'    => (double)$account->amount,
            'user_id'   => (int)$account->user_id
      /*"number": "123456789",
      "alias": "alias1",
      "linked": "0",
      "currency": "EUR",
      "amount": "10000",
      "enterprise": null,
      "center": null,
      "product": null,
      "contract_number": null,
      "connection_enterprise": null,
      "connection_center": null,
      "connection_product": null,
      "connection_contract_number": null,
      "connection_person_type": null,
      "connection_marco_channel": null,
      "connection_person_code": null,
      "user_id": "1",
      "created_at": "2017-04-06 13:22:47",
      "updated_at": "2017-04-06 13:22:47"*/
        ];
    }
}