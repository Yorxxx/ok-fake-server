<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;

class AccountsController extends Controller
{
    use Helpers;

    /**
     * Returns the accounts
     * @GET('/api/accounts')
     * @Response(200, $accounts)
     */
    public function getAccounts() {
        return $this->response()->created();
    }
}
