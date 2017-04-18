<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

        try {

            $rules = [
                'currency_destination'  => 'required|In:EUR,GBP',
                'currency_source'  => 'required|In:EUR,GBP'
            ];

            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                throw new BadRequestHttpException($v->getMessageBag()->first());
            }

            $dest = $request->get('currency_destination');
            $source = $request->get('currency_source');

            if (strcmp($source, "EUR") == 0 && strcmp($dest, "GBP") == 0) {
                return "0.85";
            }
            if (strcmp($source, "GBP") == 0 && strcmp($dest, "EUR") == 0) {
                return "1.17";
            }

            return "1";

        } catch (BadRequestHttpException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        }
    }
}
