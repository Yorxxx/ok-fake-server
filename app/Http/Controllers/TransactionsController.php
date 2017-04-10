<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Transformers\TransactionsTranformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use JWTAuth;


class TransactionsController extends Controller
{
    use Helpers;

    /**
     * Returns the transactions for the current user
     * @GET('/api/transactions')
     * @Response(200, $transactions)
     */
    public function getTransactions() {

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
        $data = $user->transactions; //Transaction::where('user_id', $user->id)->get();
        return $this->collection($data, new TransactionsTranformer, ['key' => 'results']);
        //return $this->response()->accepted();
    }
}