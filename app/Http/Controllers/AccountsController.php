<?php

namespace App\Http\Controllers;

use App\Account;
use App\User;
use App\Transformers\AccountsTranformer;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use JWTAuth;

class AccountsController extends Controller
{
    use Helpers;

    /**
     * Returns the accounts
     * @GET('/api/accounts')
     * @Response(200, $accounts)
     */
    public function getAccounts() {
        try {
            $token = JWTAuth::getToken();
            if (!$user = JWTAuth::toUser($token)) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        $accounts = Account::where('user_id', $user->id)->get();
        //return $this->response->array(['data' => $accounts], 200);
        return $this->collection($accounts, new AccountsTranformer);
    }

    /**
     * TODO unknown the purpose of this method
     * @POST('/api/accounts/{id}/link')
     * @Response(200)
     */
    public function link($id) {
        try {
            $token = JWTAuth::getToken();
            if (!$user = JWTAuth::toUser($token)) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        if (strcmp($id, $user->id) != 0)
            return $this->response->errorForbidden();
        return $this->response->accepted();
    }
}
