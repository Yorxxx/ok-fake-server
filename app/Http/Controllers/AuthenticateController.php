<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthenticateController extends Controller
{

    use Helpers;

    /**
     * Authenticates a user
     * @param Request $request the request data
     * @Post("/authenticate")
     * @Request("email=foo&password=bar", contentType="application/x-www-form-urlencoded")
     * @Response(200, body={"token"=<token>}
     */
    public function authenticate(Request $request) {

        if (!$request->has('document') || !$request->has('doctype')) {
            return $this->response->errorBadRequest();
        }
        $doctype = $request->get('doctype');
        if ($doctype !== "N" && $doctype !== "P") {
            return $this->response->errorBadRequest("Unsupported doctype");
        }

        $credentials = ['doctype' email' => $request->get('document'), 'password' => $request->get('password')];
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

    public function getAuthenticatedUser() {
        //return $this->response->accepted();

        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }
}
