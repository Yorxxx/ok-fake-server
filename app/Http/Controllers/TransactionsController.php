<?php

namespace App\Http\Controllers;

use App\Account;
use App\Agent;
use App\Repositories\SMSRepositoryInterface;
use App\Transaction;
use App\Transformers\TransactionsTranformer;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class TransactionsController extends AuthController
{
    protected $smsProvider;

    /**
     * TransactionsController constructor.
     */
    public function __construct(SMSRepositoryInterface $smsProvider)
    {
        $this->smsProvider = $smsProvider;
    }


    /**
     * Returns the transactions for the current user
     * TODO just for faking purposes, pending transactions created more than 24h ago are updated to confirmed. This should be faked in another way, like a cron job.
     * @GET('/api/transactions')
     * @Response(200, $transactions)
     */
    public function getTransactions(Request $request) {
        $user = $this->getUserFromToken();
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

        if (array_key_exists('frequency', $request->all()) && $request->all()['frequency']) {
            $data = Transaction::where('user_id', $user->id)
                ->orderBy('frequency', 'desc')
                ->get();
        }
        else {
            $data = Transaction::where('user_id', $user->id)
                ->orderBy('date_creation', 'desc')
                ->get();
        }
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
     * Checks the currency for a given transaction
     * TODO for faking purposes, does not modify the estimated amount, and just returns the existing data
     * @param $id Integer the identifier of the transaction to check
     * @POST('/api/transactions/{id}/check_currency')
     */
    public function check_currency($id) {
        return self::show($id);
    }

    /**
     * Increseas the frequency of the given transaction
     * @POST('/transactions/{id}/increase_frequency')
     * @param $id Integer the identifier of the transaction
     */
    public function increase_frequency($id) {
        $user = $this->getUserFromToken();
        $data = Transaction::where('id', $id)->first();
        if (!$data) {
            return $this->response()->errorNotFound("Transaction not found");
        }
        if (strcmp($user->id, "".$data->user_id) != 0) {
            return $this->response->errorForbidden("User does not have permissions to access this transaction");
        }
        $data->frequency++;
        $data->save();
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
            $values['account_source'] = $emisor->id;
            $values['state'] = $agent_dest->account == NULL ? 7 : 0;
            if ($transaction = Transaction::create($values)) {
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
        return ['positions'     => self::unique_sort_array($positions),
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

        try {
            $rules = [
                'signatureData'         => 'required',
                'signaturePositions'    => 'required'
            ];
            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                throw new BadRequestHttpException($v->getMessageBag()->first());
            }

            $current_user = $this->getUserFromToken();
            $transaction = Transaction::where('id', $id)->first();
            if ($transaction == null) {
                throw new ModelNotFoundException("Transaction does not exist");
            }
            if (strcmp($current_user->id, $transaction->user_id) != 0) {
                throw new UnauthorizedException("User does not have permissions to access this transaction");
            }

            $ticket = $this->generateRandomString();
            $transaction->ticket_otp = $ticket;
            $transaction->save();

            $this->smsProvider->send("Your verification code is " . $ticket, $current_user->phone);
            return ['ticket' => $ticket];
        } catch (BadRequestHttpException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound($e->getMessage());
        } catch (UnauthorizedException $e) {
            return $this->response->errorForbidden($e->getMessage());
        }
    }

    function in_array_all($value, $array)
    {
        return (reset($array) == $value && count(array_unique($array)) == 1);
    }

    /**
     * Generates a unique (ie, a number can only be once within the array), sorted by number.
     * @param array $array the array to sort and unique.
     * @return array an array sorted an unique.
     */
    public function unique_sort_array(array $array) {
        sort($array, SORT_NUMERIC);
        $data = array_unique($array, SORT_NUMERIC);
        return array_values($data);
    }

    /**
     * Generates a random string
     * @param int $length the desired length of string
     * @return string the random string
     */
    function generateRandomString($length = 6) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
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

        try {
            $current_user = $this->getUserFromToken();

            $rules = [
                'otpSmsCode' => 'required'
            ];
            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                throw new BadRequestHttpException($v->getMessageBag()->first());
            }

            $transaction = Transaction::where('id', $id)->first();
            if ($transaction == null) {
                throw new NotFoundHttpException("Transaction does not exist");
            }
            if (strcmp($current_user->id, $transaction->user_id) != 0) {
                throw new UnauthorizedException("User does not have permissions to access this transaction");
            }

            if (env('SMS_PROVIDER') != null && strcmp($request['otpSmsCode'], $transaction->ticket_otp) != 0) {
                throw new UnauthorizedException("The supplied code is incorrect");
            }

            $transaction->state = 5;
            $transaction->source->amount -= $transaction->amount_source;
            $transaction->save();
            $transaction->source->save();
        } catch (BadRequestHttpException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->response->errorNotFound($e->getMessage());
        } catch (UnauthorizedException $e) {
            return $this->response->errorForbidden($e->getMessage());
        } catch( Exception $e) {
            //@codeCoverageIgnoreStart
            return $this->response->errorInternal($e->getMessage());
            //@codeCoverageIgnoreEnd
        }
        return;
    }
}
