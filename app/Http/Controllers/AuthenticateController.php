<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthenticateController extends AuthController
{

    /**
     * Authenticates a user
     * @Post("/authenticate")
     * @Request(email=foo&password=bar, contentType="application/x-www-form-urlencoded")
     * @Response(200, body={token=<token>}
     * @param Request $request the request data
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function authenticate(Request $request) {

        try {
            $rules = [
                'document' => 'required',
                'doctype' => 'required|In:P,N',
                'password' => 'required|Size:4'
            ];

            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                throw new BadRequestHttpException($v->getMessageBag()->first());
            }

            /*$credentials = ['document' => $request->get('document'), 'password' => $request->get('password'), 'doctype' => $request->get('doctype')];*/
            $credentials = $request->all();
            if (!$token = JWTAuth::attempt($credentials))
                throw new AuthenticationException();

            return response()->json(compact('token'));
        } catch (BadRequestHttpException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        } catch (AuthenticationException $e) {
            return $this->response->errorUnauthorized('Invalid credentials');
        } catch (Exception $e) {
            return $this->response->errorInternal("Could not create token");
        }
    }

    /**
     * Returns the authenticated user
     * @Get("/users/me")
     * @Response(200, body={"user"=> $user }
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticatedUser()
    {
        $user = $this->getUserFromToken();
        // the token is valid and we have found the user via the sub claim
        $first_name = '';
        if ($user->name !== null) {
            $first_name = array_values(preg_split('~\s+~', $user->name, -1, PREG_SPLIT_NO_EMPTY))[0];
        }

        $prefix = $phone = '';
        if ($user->phone !== null) {
            $phone_values = explode('-', $user->phone);
            $prefix = array_values($phone_values)[0];
            $phone = array_values($phone_values)[1];
        }
        $data = [
            'user' => [
                'username' => $user->document,
                'created_at' => $user->created_at->toDateTimeString(),
                'documentType' => $user->doctype,
                'email' => $user->email,
                'id' => $user->id,
                'first_name' => $first_name,
                'updated_at' => $user->updated_at->toDateTimeString(),
                'phone' => [
                    'prefix' => $prefix,
                    'phone' => $phone
                ]
            ]
        ];

        return response()->json($data);
    }
}
