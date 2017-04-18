<?php

namespace Tests\Feature;

use Tests\BrowserKitTestCase;

class CurrencyControllerTest extends BrowserKitTestCase
{
    /**
     * @test
     * Test: GET: /api/currency
     * Not specifying the currency destination, returns an error
     */
    public function given_missingCurrencyDestination_When_getCurrency_Then_Returns400()
    {
        $this->get('/api/currency')
            ->seeStatusCode(400)
            ->seeText("The currency destination field is required");
    }

    /**
     * @test
     * Test: GET: /api/currency
     * Not specifying the currency source, returns an error
     */
    public function given_missingCurrencySource_When_getCurrency_Then_Returns400()
    {
        $this->get('/api/currency?currency_destination=EUR')
            ->seeStatusCode(400)
            ->seeText("The currency source field is required");
    }

    /**
     * @test
     * TEST: GET /api/currency&currency_destination=foo&currency_source=bar
     * Requesting a not supported currency destination, returns an error
     */
    public function given_unknownCurrencyDest_When_GetCurrency_Then_Returns400() {

        $this->get('/api/currency?currency_destination=foo&currency_source=bar')
            ->seeStatusCode(400)
            ->seeText('The selected currency destination is invalid');
    }

    /**
     * @test
     * TEST: GET /api/currency&currency_destination=EUR&currency_source=bar
     * Requesting a not supported currency source, returns an error
     */
    public function given_unknownCurrencySource_When_GetCurrency_Then_Returns400() {

        $this->get('/api/currency?currency_destination=EUR&currency_source=bar')
            ->seeStatusCode(400)
            ->seeText('The selected currency source is invalid');
    }

    /**
     * @test
     * TEST: GET /api/currency&currency_destination=EUR&currency_source=GBP
     */
    public function given_sameCurrency_When_GetCurrency_Then_Returns1() {
        $this->get('/api/currency?currency_destination=EUR&currency_source=EUR')
            ->seeStatusCode(200)
            ->see("1");
    }

    /**
     * @test
     * TEST: GET /api/currency&currency_destination=EUR&currency_source=GBP
     */
    public function given_EURtoGBP_When_GetCurrency_Then_Returns085() {
        $this->get('/api/currency?currency_destination=GBP&currency_source=EUR')
            ->seeStatusCode(200)
            ->see("0.85");
    }

    /**
     * @test
     * TEST: GET /api/currency&currency_destination=EUR&currency_source=GBP
     */
    public function given_GBPtoEUR_When_GetCurrency_Then_Returns085() {
        $this->get('/api/currency?currency_destination=EUR&currency_source=GBP')
            ->seeStatusCode(200)
            ->see("1.17");
    }
}
