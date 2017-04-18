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
use Illuminate\Contracts\Queue\EntityNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class TransactionsController extends AuthController
{
    /**
     * Returns the transactions for the current user
     * TODO just for faking purposes, pending transactions created more than 24h ago are updated to confirmed. This should be faked in another way, like a cron job.
     * @GET('/api/transactions')
     * @Response(200, $transactions)
     */
    public function getTransactions() {
        $data = $this->getUserFromToken()->transactions;

        foreach ($data as $transaction) {
            if ($transaction->state == 5) {
                $filter = Carbon::yesterday();
                if ($filter->gt($transaction->date_creation)) {
                    $transaction->state = 3;
                    $transaction->save();
                }
            }
        }
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
            $rules = [
                'emisor_account'        => 'required',
                'agent_destination'     => 'required',
                'amount'                => 'Numeric|Min:1|Max:499',
                'amount_estimated'      => 'Numeric|Min:1|Max:499',
                'currency_source'       => 'required|In:EUR,GBP',
                'currency_destination'  => 'required|In:EUR,GBP'
            ];
            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                throw new BadRequestHttpException($v->getMessageBag()->first());
            }

            $current_user = $this->getUserFromToken();

            $emisor = Account::where('id', $request['emisor_account'])->first();
            if ($emisor == null) {
                throw new ModelNotFoundException('This account does not exist');
            }
            if (strcmp($current_user->id, $emisor->user_id) != 0) {
                throw new UnauthorizedException("Current user should match emisor");
            }
            $agent_dest = Agent::where('id', $request['agent_destination'])->first();
            if ($agent_dest == null) {
                throw new ModelNotFoundException('This agent does not exist');
            }
            if (strcmp($emisor->number, $agent_dest->account) == 0) {
                throw new UnauthorizedException("Destination account cannot be the same as emisor account");
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

        } catch (BadRequestHttpException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound($e->getMessage());
        } catch (UnauthorizedException $e) {
            return $this->response->errorForbidden($e->getMessage());
        } catch( Exception $e) {
            //@codeCoverageIgnoreStart
            return $this->response->errorInternal($e->getMessage());
            //@codeCoverageIgnoreEnd
        }
    }

    /**
     * Returns the positions from the given transaction
     * @GET('/api/transactions/{id}/signature_positions')
     * @Response(200, {positions})
     * @param $id Integer identifier of the transaction
     * @return array
     */
    public function signaturePositions($id) {

        $current_user = $this->getUserFromToken();
        $transaction = Transaction::where('id', $id)->first();
        if ($transaction == null) {
            return $this->response->errorNotFound("Transaction does not exist");
        }
        if (strcmp($current_user->id, $transaction->user_id) != 0) {
            return $this->response->errorForbidden("User does not have permissions to access this transaction");
        }

        $positions = [random_int(1, 8), random_int(1, 8), random_int(1, 8), random_int(1, 8)];
        sort($positions, SORT_NUMERIC);
        return ['positions'     => array_unique($positions, SORT_NUMERIC),
            'signatureLength'   => 8];
    }

    /**
     * Sends an OTP requesting to confirmation
     * @POST('/api/transactions/{id}/signature_otp')
     * @Request("signature_positions=[foo1, foo2, foo3]&signatureData=bar"
     * @Response(200, {"ticket"="foo"})
     * @param Request $request
     * @param $id Integer the transaction identifier to confirm
     * @return \Dingo\Api\Http\Response|void
     */
    public function signatureOtp(Request $request, $id) {

        $current_user = $this->getUserFromToken();
        $transaction = Transaction::where('id', $id)->first();
        if ($transaction == null) {
            return $this->response->errorNotFound("Transaction does not exist");
        }
        if (strcmp($current_user->id, $transaction->user_id) != 0) {
            return $this->response->errorForbidden("User does not have permissions to access this transaction");
        }

        return ['ticket'    => $this->generateRandomString()];
    }

    /**
     * Generates a random string
     * @param int $length the desired length of string
     * @return string the random string
     */
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Validates the signature
     * @POST('/api/transactions/{id}/signature_confirmation')
     * @Request("optSmsCode=foo"}
     * @Response(200)
     * @param Request $request
     * @param $id Integer the transaction identifier to confirm
     * @return \Dingo\Api\Http\Response|void
     */
    public function signatureConfirmation(Request $request, $id) {

        $current_user = $this->getUserFromToken();
        $transaction = Transaction::where('id', $id)->first();
        if ($transaction == null) {
            return $this->response->errorNotFound("Transaction does not exist");
        }
        if (strcmp($current_user->id, $transaction->user_id) != 0) {
            return $this->response->errorForbidden("User does not have permissions to access this transaction");
        }
        $transaction->state = 5;
        $transaction->save();

        return;
    }
}
