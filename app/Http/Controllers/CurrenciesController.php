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
            return $this->response->errorBadRequest("Missing required params");
        }

        $allowed_currencies = ["EUR", "GBP"];

        $dest = $request->get('currency_destination');
        if (!in_array($dest, $allowed_currencies)) {
            return $this->response->errorBadRequest("Unknown currency " . $dest);
        }

        $source = $request->get('currency_source');
        if (!in_array($source, $allowed_currencies)) {
            return $this->response->errorBadRequest("Unknown currency " . $source);
        }

        if (strcmp($source, "EUR") == 0 && strcmp($dest, "GBP") == 0) {
            return "0.85";
        }
        if (strcmp($source, "GBP") == 0 && strcmp($dest, "EUR") == 0) {
            return "1.17";
        }

        return "1";
    }
}
