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
     * Marks the account as linked (ie: linked = true)
     * @POST('/api/accounts/{id}/link')
     * @Response(200)
     */
    public function link($id) {
        $user = $this->getUserFromToken();
        $accounts = $user->accounts;

        foreach ($accounts as $account) {
            if (strcmp($id, $account->id) == 0) {
                $account->linked = true;
                $account->save();
                return $this->response->accepted();
            }
        }
        return $this->response->errorForbidden("Cannot link an account that does not belongs to you");
    }

    /**
     * Marks the account as unlinked (ie: linked = false)
     * @POST('/api/accounts/{id}/unlink')
     * @Response(200)
     */
    public function unlink($id) {
        $user = $this->getUserFromToken();
        $accounts = $user->accounts;

        foreach ($accounts as $account) {
            if (strcmp($id, $account->id) == 0) {
                $account->linked = false;
                $account->save();
                return $this->response->accepted();
            }
        }
        return $this->response->errorForbidden("Cannot unlink an account that does not belongs to you");
    }
}
