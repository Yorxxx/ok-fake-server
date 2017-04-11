<?php

namespace App\Http\Controllers;

use App\Setting;
use Dingo\Api\Routing\Helpers;
use App\Transformers\SettingsTranformer;



class SettingsController extends AuthController
{
    use Helpers;

    /**
     * Returns the settings for the current user
     * @GET('/api/settings')
     * @Response(200, $settings)
     */
    public function getSettings() {
        $user = $this->getUserFromToken();
        $settings = Setting::where('user_id', $user->id)->get();
        return $this->collection($settings, new SettingsTranformer, ['key' => 'data']);
    }
}
