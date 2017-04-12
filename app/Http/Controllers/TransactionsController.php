<?php

namespace App\Http\Controllers;

use App\Account;
use App\Agent;
use App\Transaction;
use App\Transformers\TransactionsTranformer;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Exception;


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

    /**
     * Stores a new transaction
     * @POST('/api/transactions')
     * @param Request $request the request
     * @return \Dingo\Api\Http\Response
     */
    public function store(Request $request) {
        try {
            $this->validate($request, [
                'emisor_account'        => 'required',
                'agent_destination'     => 'required',
                'amount'                => 'Numeric|Min:1',
                'amount_estimated'      => 'Numeric|Min:1',
                'currency_source'       => 'required|In:EUR,GBP',
                'currency_destination'  => 'required|In:EUR,GBP'
            ]);

            $current_user = $this->getUserFromToken();

        } catch (Exception $e) {
            return $this->response->errorBadRequest();
        }
        $emisor = Account::where('id', $request['emisor_account'])->first();
        if ($emisor == null) {
            return $this->response->errorNotFound('This account does not exist');
        }
        if (strcmp($current_user->id, $emisor->user_id) != 0) {
            return $this->response->errorForbidden();
        }
        $agent_dest = Agent::where('id', $request['agent_destination'])->first();
        if ($agent_dest == null) {
            return $this->response->errorNotFound('This agent does not exist');
        }

        if (strcmp($emisor->number, $agent_dest->account) == 0) {
            return $this->response->errorForbidden("Destination account cannot be the same as emisor account");
        }

        $transformer = new TransactionsTranformer;
        $values = $transformer->mapFromRequest($request->all());
        $values['user_id'] = $current_user->id;
        $values['date_start'] = Carbon::now();
        $values['date_creation'] = Carbon::now();
        $values['date_end'] = Carbon::now()->addDays(7);
        if ($transaction = Transaction::create($values)) {
            $emisor->amount-=$request['amount'];
            $emisor->save();
            return $this->response->item($transaction, $transformer);
        }


        return $this->response->errorInternal();
    }
}
