<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use Helpers;

    /**
     * Returns the settings for the current user
     * @GET('/api/settings')
     * @Response(200, $settings)
     */
    public function getSettings() {
        return $this->response->created();
    }
}
