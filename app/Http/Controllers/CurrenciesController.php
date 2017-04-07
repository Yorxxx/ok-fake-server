<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

class CurrenciesController extends Controller
{
    use Helpers;

    /**
     * Returns the currencies based on the supplied params for the current user
     * @GET('/api/currency')
     * @Request("currency_destination=foo&currency_source=bar", contentType="application/x-www-form-urlencoded")
     * @Response(200, $settings)
     */
    public function getCurrency(Request $request) {

        if (!$request->has('currency_destination') || !$request->has('currency_source')) {
            return $this->response->errorBadRequest();
        }
        return $this->response->errorBadRequest("Unknown currency");

//        try {
//            $token = JWTAuth::getToken();
//            if (!$user = JWTAuth::toUser($token)) {
//                return response()->json(['user_not_found'], 404);
//            }
//        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
//            return response()->json(['token_expired'], $e->getStatusCode());
//        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
//            return response()->json(['token_invalid'], $e->getStatusCode());
//        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
//            return response()->json(['token_absent'], $e->getStatusCode());
//        }
        $settings = Setting::where('user_id', $user->id)->get();
        return $this->collection($settings, new SettingsTranformer, ['key' => 'data']);
    }
}
