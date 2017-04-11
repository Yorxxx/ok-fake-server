<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthenticateController extends AuthController
{

    use Helpers;

    /**
     * Authenticates a user
     * @Post("/authenticate")
     * @Request(email=foo&password=bar, contentType="application/x-www-form-urlencoded")
     * @Response(200, body={token=<token>}
     * @param Request $request the request data
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function authenticate(Request $request) {

        if (!$request->has('document') || !$request->has('doctype')) {
            return $this->response->errorBadRequest();
        }
        $doctype = $request->get('doctype');
        if ($doctype !== "N" && $doctype !== "P") {
            return $this->response->errorBadRequest("Unsupported doctype");
        }

        $credentials = ['document' => $request->get('document'), 'password' => $request->get('password'), 'doctype' => $request->get('doctype')];
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        // all good so return the token
        return response()->json(compact('token'));
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
