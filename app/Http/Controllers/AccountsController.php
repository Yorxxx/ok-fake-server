<?php

namespace App\Http\Controllers;

use App\Account;
use App\Transformers\AccountsTranformer;
use Dingo\Api\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    /**
     * Shows an account by its number
     * @param Request $request
     * @POST('/api/accounts/by_number')
     * @Request({account="foo"})
     */
    public function show(Request $request) {

        try {
            $rules = [
                'account'   => 'required|max:255',
            ];

            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                throw new BadRequestHttpException($v->getMessageBag()->first());
            }
            $account = Account::where('number', $request->get('account'))->first();
            if (!$account) {
                throw new ModelNotFoundException();
            }
         
            $prefix = $phone = '';
            if ($account->phone !== null) {
                $phone_values = explode('-', $agent->phone);
                $prefix = array_values($phone_values)[0];
                $phone = array_values($phone_values)[1];
            }
            return [
                'id'                => $account->id,
                'account'           => $account->number,
                'owner'             => true,
                'name'              => $account->alias,
                'email'             => $account->user->email,
                'country'           => $account->user->country,
                'prefix'            => $prefix,
                'phone'             => $phone,
                'user_id'           => $account->user->id
            ];
            //return $this->item($agent, new AccountsTranformer);

        } catch(BadRequestHttpException $e) {
            return $this->response()->errorBadRequest($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->response()->errorNotFound($e->getMessage());
        }
            // @codeCoverageIgnoreStart
        catch(Exception $e) {
            return $this->response()->errorInternal();
        }
        // @codeCoverageIgnoreEnd
    }
}
