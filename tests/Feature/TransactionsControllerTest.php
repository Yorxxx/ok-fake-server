<?php

use Tests\BrowserKitTestCase;
use App\Agent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TransactionsControllerTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Test: GET: /api/transactions
     * Requesting a detailed transaction without authorization, should return 401
     */
    public function given_noAuthorization_When_getTransactions_Then_Returns401()
    {
        $this->get('/api/transactions')->seeStatusCode(401);
    }

    /**
     * @test
     * Test: GET: /api/transactions
     * Requesting transactions for current user without having performed any of them, should return an empty list
     */
    public function given_authorizedUserWithoutTransactions_When_getTransactions_Then_ReturnsEmptyList() {

        $user = factory(App\User::class)->create();

        $this->get('/api/transactions', $this->headers($user))
            ->seeStatusCode(200)
            ->seeJson([
                "results" => []
            ]);
    }

    /**
     * @test
     * Test: GET: /api/transactions
     * Requesting user transactions should return a list containing all the transactions associated to the user
     */
    public function given_authorizedUserWithTransactions_When_getTransactions_Then_ReturnsUserTransactions() {

        $user = factory(App\User::class)->create();
        $dest_agent = factory(App\Agent::class)->create();

        factory(App\Transaction::class)->create([
            'user_id' => $user->id,
            'agent_source' => $dest_agent->account,
            'agent_destination' => $dest_agent->id
        ]);

        $this->get('/api/transactions', $this->headers($user))
            ->seeStatusCode(200)
            ->seeJsonStructure([
                "results" => [
                    '*' => [
                        'id', 'date_start', 'date_end', 'date_creation', 'amount_destination', 'amount_estimated',
                        'state', 'concept', 'currency_destination', 'amount_source', 'currency_source'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * Test: GET: /api/transactions/{id}
     * Requesting a transaction by a non-existing id, should return error
     */
    public function given_notFoundTransactionId_When_show_Then_ReturnsNotFoundError() {

        $user = factory(App\User::class)->create();

        // Arrange
        $source_agent = factory(App\Agent::class)->create();
        $dest_agent = factory(App\Agent::class)->create();

        factory(App\Transaction::class)->create([
            'id'                => 10,
            'user_id'           => $user->id,
            'agent_source'      => $source_agent->id,
            'agent_destination' => $dest_agent->id
        ]);

        // Act
        $result = $this->get('/api/transactions/100', $this->headers($user));

        // Assert
        $result
            ->seeStatusCode(404)
            ->seeText("Transaction not found");
    }

    /**
     * @test
     * Test: GET: /api/transactions/{id}
     * Requesting a transaction by id without Authorization should be forbidden
     */
    public function given_noAuthorization_When_show_Then_Returns401() {

        // Act
        $result = $this->get('/api/transactions/100');

        // Assert
        $result->seeStatusCode(401);
    }

    /**
     * @test
     * TEST: GET /api/transactions/{id}
     * Requesting a transaction by id with Authorization but not performed by user, should be forbidden
     */
    public function given_notAuthorizedTransaction_When_Show_Then_Returns403() {

        // Arrange
        $currentUser = factory(App\User::class)->create();

        $user = factory(App\User::class)->create();
        $source_agent = factory(App\Agent::class)->create();
        $dest_agent = factory(App\Agent::class)->create();

        factory(App\Transaction::class)->create([
            'id'                => 10,
            'user_id'           => $user->id,
            'agent_source'      => $source_agent->id,
            'agent_destination' => $dest_agent->id
        ]);

        // Act
        $result = $this->get('/api/transactions/10', $this->headers($currentUser, "abc"));

        // Assert
        $result->seeStatusCode(403);
    }

    /**
     * @test
     * TEST: GET /api/transactions/{id}
     * Requesting an authorized transaction by id performed by current user, should return the transaction details
     */
    public function given_AuthorizedExistingTransaction_When_Show_Then_ReturnsTransaction() {

        // Arrange
        $user = factory(App\User::class)->create();

        $dest_agent = factory(App\Agent::class)->create();
        $transaction = factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'agent_source'      => $dest_agent->account,
            'agent_destination' => $dest_agent->id
        ]);

        // Act
        $result = $this->get('/api/transactions/' . $transaction->id, $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                'id', 'date_start', 'date_end', 'date_creation', 'state', 'concept', 'agent_destination', 'agent_source',
                'amount_source', 'currency_source', 'amount_destination', 'amount_estimated', 'currency_destination'
            ]);
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Posting a new transaction when not logged in is forbidden
     */
    public function given_noAuthorization_When_store_Then_Returns401() {

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => 534,
            'agent_destination'         => 4256,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ]);

        // Assert
        $result->seeStatusCode(401);
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Storing a transaction with missing params is not allowed
     */
    public function given_invalidInput_When_Store_Then_ReturnsBadRequest() {

        // Arrange
        $user = factory(App\User::class)->create();

        // Act
        $result = $this->post('/api/transactions/', [
            'agent_destination'         => 4256,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400);
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Storing a transaction from an account that does not belong to the user, is forbidden
     */
    public function given_foreignAccount_When_Store_Then_ReturnsForbidden() {

        // Arrange
        $user = factory(App\User::class)->create();

        $external_user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $external_user->id
        ]);

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => $account->id,
            'agent_destination'         => 4256,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(403)
            ->seeText("Forbidden");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Storing a transaction from an account that does not exist, is not allowed
     */
    public function given_nonExistingAccount_When_Store_Then_Returns404() {
        // Arrange
        $user = factory(App\User::class)->create();

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => 50,
            'agent_destination'         => 4256,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(404)
            ->seeText("This account does not exist");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Storing a transaction to an agent that does not exist, is not allowed
     */
    public function given_nonExistingAgent_When_Store_Then_Returns404() {
        // Arrange
        $user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => $account->id,
            'agent_destination'         => 4256,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(404)
            ->seeText("This agent does not exist");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Trying to send data to himself is not allowed
     */
    public function given_destinationAccountIsEmissor_When_Store_Then_Returns403() {
        // Arrange
        $user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id,
            'number'    => '1234'
        ]);
        $agent = factory(App\Agent::class)->create([
            'account'   => '1234',
            'user_id'   => $user->id
        ]);


        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => $account->id,
            'agent_destination'         => $agent->id,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(403)
            ->seeText("Destination account cannot be the same as emisor account");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Trying to send negative amounts is forbidden
     */
    public function given_negativeAmount_When_Store_Then_Returns405() {
        // Arrange
        $user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);
        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => $account->id,
            'agent_destination'         => $agent->id,
            'concept'                   => 'foo',
            'amount'                    => -5,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("Bad Request");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Trying to receive negative amounts it not allowed
     */
    public function given_negativeEstimatedAmount_When_Store_Then_Returns405() {
        // Arrange
        $user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);
        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => $account->id,
            'agent_destination'         => $agent->id,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "-42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("Bad Request");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Trying to send currencies aside EUR and GBP is not allowed
     */
    public function given_unsupportedCurrencySource_When_Store_Then_Returns405() {
        // Arrange
        $user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);
        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => $account->id,
            'agent_destination'         => $agent->id,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "-42.5",
            'currency_source'           => 'USD',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("Bad Request");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Trying to received currencies aside EUR and GBP is not allowed
     */
    public function given_unsupportedCurrencyDestination_When_Store_Then_Returns405() {
        // Arrange
        $user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);
        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => $account->id,
            'agent_destination'         => $agent->id,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "-42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'USD'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("Bad Request");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Posting a new transaction stores the data and decrements emisor amount.
     */
    public function given_validTransaction_When_Store_Then_StoresNewTransaction() {
        // Arrange
        $user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);
        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id
        ]);

        $previous_transactions = \App\Transaction::all();

        // Act
        $result = $this->post('/api/transactions/', [
            'emisor_account'            => $account->id,
            'agent_destination'         => $agent->id,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                'id', 'agent_destination', 'agent_source', 'date_start', 'date_end', 'date_creation', 'amount_destination',
                'amount_estimated', 'state', 'concept', 'currency_destination', 'amount_source', 'currency_source'
            ]);

        $updated_transactions = \App\Transaction::all();
        // Check that has incremented the number of transactions
        self::assertTrue($updated_transactions->count() == $previous_transactions->count()+1);

        // Check that the emissor amount has been decreased
        $updated_account = \App\Account::where('id', $account->id)->first();
        self::assertFalse($updated_account->amount == $account->amount);
        self::assertEquals($updated_account->amount, $account->amount-50);
    }

    /**
     * @test
     * Cannot retrieve transaction positions without authorization
     */
    public function given_noAuthorization_When_GetPositions_Then_Returns401() {

        $result = $this->get('/api/transactions/50/signature_positions');

        // Assert
        $result->seeStatusCode(401);
    }
}
