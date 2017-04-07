<?php

namespace Tests\Feature;

use Tests\BrowserKitTestCase;

class CurrencyControllerTest extends BrowserKitTestCase
{
    /**
     * @test
     * Test: GET: /api/currency
     */
    public function given_noParams_When_getCurrency_Then_Returns400()
    {
        $this->get('/api/currency')->seeStatusCode(400);
    }

    /**
     * @test
     * TEST: GET /api/currency
     */
    public function given_unknownCurrency_When_GetCurrency_Then_Returns400() {

        $this->get('/api/currency?currency_destination=foo&currency_source=bar')
            ->seeStatusCode(400)
            ->seeText('Unknown currency');
    }
}
