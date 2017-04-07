<?php

namespace App\Http\Controllers;

use App\Setting;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use App\Transformers\SettingsTranformer;
use JWTAuth;


class SettingsController extends Controller
{
    use Helpers;

    /**
     * Returns the settings for the current user
     * @GET('/api/settings')
     * @Response(200, $settings)
     */
    public function getSettings() {

        try {
            $token = JWTAuth::getToken();
            if (!$user = JWTAuth::toUser($token)) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        $settings = Setting::where('user_id', $user->id)->get();
        return $this->collection($settings, new SettingsTranformer, ['key' => 'data']);
    }
}
