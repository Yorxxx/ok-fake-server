<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Transformers\TransactionsTranformer;
use Dingo\Api\Routing\Helpers;


class TransactionsController extends AuthController
{
    /**
     * Returns the transactions for the current user
     * @GET('/api/transactions')
     * @Response(200, $transactions)
     */
    public function getTransactions() {
        $data = $this->getUserFromToken()->transactions;
        return $this->collection($data, new TransactionsTranformer, ['key' => 'results']);
    }

    /**
     * Returns the transaction with the specified id
     * @GET('/api/transactions/{id}')
     * @Response(200, $transactions)
     */
    public function show($id) {
        $user = $this->getUserFromToken();
        $data = Transaction::where('id', $id)->first();
        if (!$data) {
            return $this->response()->errorNotFound("Transaction not found");
        }
        if (strcmp($user->id, "".$data->user_id) != 0) {
            return $this->response->errorForbidden();
        }
        return $this->item($data, new TransactionsTranformer);
    }
}
