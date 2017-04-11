<?php

namespace App\Http\Controllers;

use App\Account;
use App\Transformers\AccountsTranformer;
use Dingo\Api\Routing\Helpers;

class AccountsController extends AuthController
{
    /**
     * Returns the accounts
     * @GET('/api/accounts')
     * @Response(200, $accounts)
     */
    public function getAccounts() {
        $accounts = Account::where('user_id', $this->getUserFromToken()->id)->get();
        return $this->collection($accounts, new AccountsTranformer);
    }

    /**
     * TODO unknown the purpose of this method
     * @POST('/api/accounts/{id}/link')
     * @Response(200)
     */
    public function link($id) {
        if (strcmp($id, $this->getUserFromToken()->id) != 0)
            return $this->response->errorForbidden();
        return $this->response->accepted();
    }
}
