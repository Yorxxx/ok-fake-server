<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthenticateController extends Controller
{

    /**
     * Authenticates a user
     * @param Request $request the request data
     * @return \Illuminate\Http\JsonResponse
     * @Post("/authenticate")
     * @Request("email=foo&password=bar", contentType="application/x-www-form-urlencoded")
     * @Response(200, body={"token"=<token>}
     */
    public function authenticate(Request $request) {

        $credentials = $request->only('email', 'password');

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
}
