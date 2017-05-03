<?php

use App\Repositories\SMSRepositoryInterface;
use Carbon\Carbon;
use Tests\BrowserKitTestCase;
use App\Agent;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Unit\Repositories\SMSMockRepository;

class TransactionsControllerTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    public $smsrepository;

    protected function setUp()
    {
        parent::setUp();
        $this->smsrepository = new SMSMockRepository();
    }


    public function createApplication()
    {
        $app = parent::createApplication();

        // Override our app provider
        $app->singleton(SMSRepositoryInterface::class, function ($app) {
            return $this->smsrepository;
        });

        return $app;
    }


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
        $source_account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);

        $yesterday_transaction = factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'date_creation'     => Carbon::now()->subDay(1)
        ]);

        $today_transaction = factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'date_creation'     => Carbon::now()->subHour(1)
        ]);

        $lastweek_transaction = factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'date_creation'     => Carbon::now()->subWeek()
        ]);

        // Act
        $result = $this->get('/api/transactions', $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                "results" => [
                    '*' => [
                        'id', 'date_start', 'date_end', 'date_creation', 'amount_destination', 'amount_estimated',
                        'state', 'concept', 'currency_destination', 'amount_source', 'currency_source'
                    ]
                ]
            ]);

        // Check returned results are ordered by date desc
        $json_array = json_decode($result->response->content());
        $results = $json_array->results;
        self::assertEquals(3, count($results));

        $previous_date_value = Carbon::now();
        foreach ($results as $item) {
            $transaction_date = Carbon::createFromTimestamp($item->date_creation/1000);
            self::assertTrue($previous_date_value->gt($transaction_date));
            $previous_date_value = $transaction_date;
        }
        self::assertEquals($today_transaction->id, $results[0]->id);
        self::assertEquals($yesterday_transaction->id, $results[1]->id);
        self::assertEquals($lastweek_transaction->id, $results[2]->id);
    }

    /**
     * @test
     * Test: GET: /api/transactions?frequency=true
     * Requesting user transactions should return a list containing all the transactions associated to the user ordered by frequency
     */
    public function given_frequencyTransaction_When_getTransactionsByFrequency_Then_ReturnsUserTransactionsOrderedByFrequency() {

        $user = factory(App\User::class)->create();
        $dest_agent = factory(App\Agent::class)->create();
        $source_account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);

        factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'date_creation'     => Carbon::now()->subDay(1),
            'frequency'         => 2
        ]);

        factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'date_creation'     => Carbon::now(),
            'frequency'         => 20
        ]);

        factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'date_creation'     => Carbon::now()->subWeek(),
            'frequency'         => 5
        ]);

        // Act
        $result = $this->get('/api/transactions?frequency=true', $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                "results" => [
                    '*' => [
                        'id', 'date_start', 'date_end', 'date_creation', 'amount_destination', 'amount_estimated',
                        'state', 'concept', 'currency_destination', 'amount_source', 'currency_source'
                    ]
                ]
            ]);

        // Check returned results are ordered by date desc
        $json_array = json_decode($result->response->content());
        $results = $json_array->results;
        self::assertEquals(3, count($results));

        $previous_frequency_value = 100;
        foreach ($results as $item) {
            self::assertTrue($previous_frequency_value > $item->frequency);
            $previous_frequency_value = $item->frequency;
        }
    }

    /**
     * @test
     * @GET('/api/transactions')
     * Requesting user transaction that are in process and were created more than 24 hours ago, automatically updates them
     * to completed
     */
    public function given_inProcessUserTransactions_When_getTransactions_Then_UpdatesStateBasedOnTransactionDate() {

        $user = factory(App\User::class)->create();
        $dest_agent = factory(App\Agent::class)->create();
        $source_account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);

        factory(App\Transaction::class)->create([
            'user_id' => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'state' => 5,
            'date_creation' => Carbon::now()
        ]);

        $completed_transaction = factory(App\Transaction::class)->create([
            'user_id' => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'state' => 5,
            'date_creation' => Carbon::now()->subWeek()
        ]);

        // Assert
        $completed_transactions = \App\Transaction::where('state', 3)->get();
        self::assertEquals(0, $completed_transactions->count());

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

        $completed_transactions = \App\Transaction::where('state', 3)->get();
        self::assertEquals(1, $completed_transactions->count());
        self::assertEquals($completed_transactions->first()->id, $completed_transaction->id);
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
        $source_account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);

        factory(App\Transaction::class)->create([
            'id'                => 10,
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
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
        $source_account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);
        $dest_agent = factory(App\Agent::class)->create();

        factory(App\Transaction::class)->create([
            'id'                => 10,
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id
        ]);

        // Act
        $result = $this->get('/api/transactions/10', $this->headers($currentUser, "abc"));

        // Assert
        $result->seeStatusCode(403);
    }

    /**
     * @test
     * @GET /api/transactions/{id}
     * Requesting an authorized transaction by id performed by current user, should return the transaction details
     */
    public function given_AuthorizedExistingTransaction_When_Show_Then_ReturnsTransaction() {

        // Arrange
        $user = factory(App\User::class)->create();

        $source_account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);
        $dest_agent = factory(App\Agent::class)->create();
        $transaction = factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
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
            ->seeText("Current user should match emisor");
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
            ->seeText("The amount must be at least 1.");
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
            ->seeText("The amount estimated must be at least 1");
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Trying to send amounts above of 500 is not supported
     */
    public function given_moreThan500UnitsAmount_When_Store_Then_Returns405() {
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
            'amount'                    => 4200,
            'amount_estimated'          => "4200.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("The amount may not be greater than 499.");
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
            'amount_estimated'          => "42.5",
            'currency_source'           => 'USD',
            'currency_destination'      => 'EUR'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("The selected currency source is invalid");
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
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'USD'
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400)
            ->seeText("The selected currency destination is invalid");
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
        self::assertEquals(0, $updated_transactions[0]->state);

        // Check that the emissor amount has not been modified until confirmed
        $updated_account = \App\Account::where('id', $account->id)->first();
        self::assertTrue($updated_account->amount == $account->amount);
        //self::assertEquals($updated_account->amount, $account->amount-50);
    }

    /**
     * @test
     * @POST('/api/transactions/')
     * Storing a transaction whose destination agent does not have specified an account, sets the status of this account as
     * "NEED_INFO".
     */
    public function given_destinationAgentWithoutAccount_When_Store_Then_StoresTransactionAsRequiredInfo() {
        // Arrange
        $user = factory(App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id
        ]);
        $agent = factory(App\Agent::class)->create([
            'user_id'   => $user->id,
            'account'   => ''
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
        self::assertEquals(7, $updated_transactions[0]->state);

        // Check that the emissor amount has not been modified until confirmed
        $updated_account = \App\Account::where('id', $account->id)->first();
        self::assertTrue($updated_account->amount == $account->amount);
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

    /**
     * @test
     * Cannot return positions from non-existing transactions
     */
    public function given_nonExistingTransaction_When_GetPositions_Then_Returns404() {

        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        factory(\App\Transaction::class)->create([
           'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        $result = $this->get('/api/transactions/50/signature_positions', $this->headers($user));

        // Assert
        $result->seeStatusCode(404)
            ->seeText("Transaction does not exist");
    }

    /**
     * @test
     * Trying to get signature positions from transactions not performed by current user is not allowed
     */
    public function given_transactionNotPerformedByCurrentUser_When_GetPositions_Then_Returns403() {
        $current_user = factory(\App\User::class)->create();
        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        $result = $this->get('/api/transactions/' . $transaction->id . ' /signature_positions', $this->headers($current_user));

        // Assert
        $result->seeStatusCode(403)
            ->seeText("User does not have permissions to access this transaction");
    }

    /**
     * @test
     * Requesting positions for a valid transaction should returns its positions
     */
    public function given_validTransaction_When_GetPositions_Then_ReturnsPositions() {

        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        $result = $this->get('/api/transactions/' . $transaction->id . ' /signature_positions', $this->headers($user));

        // Assert

        $result->seeStatusCode(200)
            ->seeJsonStructure([
               'positions', 'signatureLength'
            ]);
    }

    /**
     * @test
     * Confirming signatures without authorization is forbidden
     */
    public function given_noAuthorization_When_signatureOtp_Then_Returns401() {

        $result = $this->post('/api/transactions/50/signature_otp', []);

        // Assert
        $result->seeStatusCode(401);
    }

    /**
     * @test
     * Cannot sign transactions that do not exist on database
     */
    public function given_nonExistingTransaction_When_signatureOtp_Then_Returns404() {

        $user = factory(\App\User::class)->create();

        // Act
        $result = $this->post('/api/transactions/50/signature_otp', [
            'signatureData'         => [1, 2, 3],
            'signaturePositions'    => [1, 2, 3]
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(404)
            ->seeText("Transaction does not exist");
    }

    /**
     * @test
     * Trying to confirm transactions not performed by current is forbidden.
     */
    public function given_transactionNotPerformedByCurrentUser_When_signatureOtp_Then_Returns403() {

        $current_user = factory(\App\User::class)->create();
        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/signature_otp', [
            'signatureData'         => [1, 2, 3],
            'signaturePositions'    => [1, 2, 3]
        ], $this->headers($current_user));

        $result->seeStatusCode(403)
            ->seeText("User does not have permissions to access this transaction");
    }

    /**
     * @test
     * If no signatureData specified, return bad request
     */
    public function given_missingSignatureData_When_signatureOtp_Then_ReturnsBadRequest() {
        $user = factory(\App\User::class)->create([
            'phone'     => '+34123456789'
        ]);
        $agent = factory(Agent::class)->create();
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/signature_otp', [
            'signaturePositions'    => [1, 2, 3]
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400);
    }

    /**
     * @test
     * If no signaturePositions specified, return bad request
     */
    public function given_missingSignaturePositions_When_signatureOtp_Then_ReturnsBadRequest() {
        $user = factory(\App\User::class)->create([
            'phone'     => '+34123456789'
        ]);
        $agent = factory(Agent::class)->create();
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/signature_otp', [
            'signatureData'    => [1, 2, 3]
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(400);
    }

    /**
     * @test
     * Confirming transactions performed by current user is allowed
     */
    public function given_validTransaction_When_signatureOtp_Then_Returns202() {

        $user = factory(\App\User::class)->create([
            'phone'     => '+34123456789'
        ]);
        $agent = factory(Agent::class)->create();
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/signature_otp', [
            'signatureData'         => [1, 2, 3],
            'signaturePositions'    => [1, 2, 3]
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure(['ticket']);

        // Check a code has been saved for this transaction
        $updated_transaction = \App\Transaction::where('id', $transaction->id)->first();
        self::assertNotNull($updated_transaction);
        self::assertNotNull($updated_transaction->ticket_otp);

        // Assert SMS was sent
        self::assertTrue($this->smsrepository->sendCalled);
        self::assertNotNull($this->smsrepository->requestedMessage);
        self::assertEquals($user->phone, $this->smsrepository->requestedDestination);
    }

    /**
     * @test
     * Cannot confirm SMS if user is not authorized
     */
    public function given_noAuthorization_When_confirmOtpSMS_Then_Returns401() {

        $result = $this->post('/api/transactions/50/signature_confirmation', []);

        // Assert
        $result->seeStatusCode(401);
    }

    /**
     * @test
     * Cannot confirm SMS transactions that do not exist on database
     */
    public function given_nonExistingTransaction_When_confirmOtpSMS_Then_Returns404() {

        $user = factory(\App\User::class)->create();
        factory(\App\Transaction::class)->create([
            'user_id'               => 10,
            'agent_destination'     => 10,
            'amount_source'         => 50,
            'ticket_otp'            => "foo"
        ]);

        // Act
        $result = $this->post('/api/transactions/50/signature_confirmation', ['otpSmsCode'=>'foo'], $this->headers($user));

        // Assert
        $result->seeStatusCode(404)
            ->seeText("Transaction does not exist");
    }

    /**
     * @test
     * Trying to confirm transactions not performed by current is forbidden.
     */
    public function given_transactionNotPerformedByCurrentUser_When_confirmOtpSMS_Then_Returns403() {

        $current_user = factory(\App\User::class)->create();
        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/signature_confirmation', ['otpSmsCode'=>'foo'], $this->headers($current_user));

        $result->seeStatusCode(403)
            ->seeText("User does not have permissions to access this transaction");
    }

    /**
     * @test
     * If no code supplied, throw error
     */
    public function given_missingRequiredParam_When_confirmOTPSms_Then_Returns403() {
        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id,
            'amount'    => 10000
        ]);
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'               => $user->id,
            'agent_destination'     => $agent->id,
            'amount_source'         => 50,
            'ticket_otp'            => "foo"
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/signature_confirmation', [], $this->headers($user));

        // Assert
        $result->seeStatusCode(400);
    }

    /**
     * @test
     * If the supplied OTP code does not match, return 403
     */
    public function given_signatureOTPMismatch_When_confirmOTPSMS_Then_Returns403() {

        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id,
            'amount'    => 10000
        ]);
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'               => $user->id,
            'agent_destination'     => $agent->id,
            'amount_source'         => 50,
            'ticket_otp'            => "foo"
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/signature_confirmation', [
            'otpSmsCode'    => "var"
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(403);
    }

    /**
     * @test
     * Confirming transactions performed by current user is allowed
     */
    public function given_validTransaction_When_confirmOtpSMS_Then_Returns200() {

        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id'   => $user->id,
            'amount'    => 10000
        ]);
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'               => $user->id,
            'agent_destination'     => $agent->id,
            'amount_source'         => 50,
            'ticket_otp'            => "foo",
            'account_source'        => $account->id
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/signature_confirmation', [
            'otpSmsCode'    => "foo"
        ], $this->headers($user));

        // Assert
        $result->seeStatusCode(200);
        $updated_transaction = \App\Transaction::where('id', $transaction->id)->first();
        self::assertNotNull($updated_transaction);
        self::assertEquals(5, $updated_transaction->state);

        // Check the emisor account has been updated
        $updated_account = \App\Account::where('id', $account->id)->first();
        self::assertEquals(9950, $updated_account->amount);
    }

    /**
     * @test
     * Cannot check currency for a transaction if not authorized
     * @POST('/api/transactions/{transaction_id}/check_currency
     */
    public function given_noAuthorization_When_checkCurrency_Then_Returns401() {

        $result = $this->post('/api/transactions/50/check_currency', []);

        // Assert
        $result->seeStatusCode(401);
    }
  
    /**
     * @test
     * If asked for currency of a transaction that do not exist, then returns 404
     * @POST('/api/transactions/{transaction_id}/check_currency
     */
    public function given_nonExistingTransactionId_When_CheckCurrency_Then_Returns404() {

        $user = factory(\App\User::class)->create();

        // Act
        $result = $this->post('/api/transactions/50/check_currency', [], $this->headers($user));

        // Assert
        $result->seeStatusCode(404)
            ->seeText("Transaction not found");
    }
  
    /**
     * @test
     * @POST('/api/transactions/{id}/check_currency')
     * Requesting the currency check for an existing transaction, returns the transaction
     */
    public function given_AuthorizedExistingTransaction_When_CheckCurrency_Then_ReturnsTransaction() {

        // Arrange
        $user = factory(App\User::class)->create();

        $source_account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);
        $dest_agent = factory(App\Agent::class)->create();
        $transaction = factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/check_currency', [], $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                'id', 'date_start', 'date_end', 'date_creation', 'state', 'concept', 'agent_destination', 'agent_source',
                'amount_source', 'currency_source', 'amount_destination', 'amount_estimated', 'currency_destination'
            ]);
    }
    
    /*
     * @test
     * Given an sorted unique array, when sorting and unique, then should return an array sorted by value asc and with no repeated elements
     */
    public function given_sortedUniqueArray_When_unique_sort_array_Then_ReturnsSortedUniqueArray() {
        $array = [0, 1, 2, 3];

        $controller = new \App\Http\Controllers\TransactionsController($this->smsrepository);

        // Act
        $result = $controller->unique_sort_array($array);

        // Assert
        self::assertNotNull($result);
        $previous = -1;
        for ($i = 0; $i<count($result); $i++) {
            $value = $result[$i];
            self::assertNotNull($value);
            self::assertFalse($previous == $value);
            self::assertTrue($previous < $value);
            $previous = $value;
        }
    }

    /*
     * @test
     * Given an sorted but repeated element array, when sorting and unique, then should return an array sorted by value asc and with no repeated elements
     */
    public function given_sortedRepeatArray_When_unique_sort_array_Then_ReturnsSortedUniqueArray() {

        $array = [50, 55, 55, 300];

        $controller = new \App\Http\Controllers\TransactionsController($this->smsrepository);

        // Act
        $result = $controller->unique_sort_array($array);

        // Assert
        self::assertNotNull($result);
        self::assertEquals(3, count($result));

        $previous = -1;
        for ($i = 0; $i<count($result); $i++) {
            $value = $result[$i];
            self::assertNotNull($value);
            self::assertFalse($previous == $value);
            self::assertTrue($previous < $value);
            $previous = $value;
        }
    }

    /*
     * @test
     * Given an unsorted array, when sorting and unique, then should return an array sorted by value asc and with no repeated elements
     */
    public function given_unsortedUniqueArray_When_unique_sort_array_Then_ReturnsSortedUniqueArray() {

        $array = [500, 55, 40, 3];

        $controller = new \App\Http\Controllers\TransactionsController($this->smsrepository);

        // Act
        $result = $controller->unique_sort_array($array);

        // Assert
        self::assertNotNull($result);
        self::assertEquals(4, count($result));

        $previous = -1;
        for ($i = 0; $i<count($result); $i++) {
            $value = $result[$i];
            self::assertNotNull($value);
            self::assertFalse($previous == $value);
            self::assertTrue($previous < $value);
            $previous = $value;
        }
    }

    /**
     * @test
     * Given an unsorted, repeated element array, when sorting and unique, then should return an array sorted by value asc and with no repeated elements
     */
    public function given_unsortedRepeatedArray_When_unique_sort_array_Then_ReturnsSortedUniqueArray() {

        $array = [500, 55, 4000, 500, 55, 3];

        $controller = new \App\Http\Controllers\TransactionsController($this->smsrepository);

        // Act
        $result = $controller->unique_sort_array($array);

        // Assert
        self::assertNotNull($result);
        self::assertEquals(4, count($result));

        $previous = -1;
        for ($i = 0; $i<count($result); $i++) {
            $value = $result[$i];
            self::assertNotNull($value);
            self::assertFalse($previous == $value);
            self::assertTrue($previous < $value);
            $previous = $value;
        }
    }

    /**
     * @test
     * Non authorized users cannot modify frequencies.
     * @POST('/api/transactions/{transaction_id}/increase_frequency
     */
    public function given_noAuthorization_When_increase_frequency_Then_Returns401() {

        $result = $this->post('/api/transactions/50/increase_frequency', []);

        // Assert
        $result->seeStatusCode(401);
    }

    /**
     * @test
     * Cannot increment the frequency of a transaction that does not exist
     * @POST('/api/transactions/{transaction_id}/increase_frequency
     */
    public function given_nonExistingTransactionId_When_increase_frequency_Then_Returns404() {

        $user = factory(\App\User::class)->create();

        // Act
        $result = $this->post('/api/transactions/50/increase_frequency', [], $this->headers($user));

        // Assert
        $result->seeStatusCode(404)
            ->seeText("Transaction not found");
    }

    /**
     * @test
     * Trying to modify transaction frequencies not performed by current is forbidden.
     */
    public function given_transactionNotPerformedByCurrentUser_When_increase_frequency_Then_Returns403() {

        $current_user = factory(\App\User::class)->create();
        $user = factory(\App\User::class)->create();
        $agent = factory(Agent::class)->create();
        $transaction = factory(\App\Transaction::class)->create([
            'user_id'                => $user->id,
            'agent_destination'     => $agent->id
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/increase_frequency', [], $this->headers($current_user));

        $result->seeStatusCode(403)
            ->seeText("User does not have permissions to access this transaction");
    }

    /**
     * @test
     * @POST('/api/transactions/{id}/increase_frequency')
     * Requesting to increment the frequency of a valid transaction is allowed if the user is logged in and is the emisor of the transaction
     */
    public function given_validTransaction_When_increase_frequency_Then_ReturnsTransaction() {

        // Arrange
        $user = factory(App\User::class)->create();

        $source_account = factory(\App\Account::class)->create([
            'user_id'       => $user->id
        ]);
        $dest_agent = factory(App\Agent::class)->create();
        $transaction = factory(App\Transaction::class)->create([
            'user_id'           => $user->id,
            'account_source'    => $source_account->id,
            'agent_destination' => $dest_agent->id,
            'frequency'         => 50
        ]);

        // Act
        $result = $this->post('/api/transactions/' . $transaction->id . '/increase_frequency', [], $this->headers($user));

        // Assert
        $result->seeStatusCode(200)
            ->seeJsonStructure([
                'id', 'date_start', 'date_end', 'date_creation', 'state', 'concept', 'agent_destination', 'agent_source',
                'amount_source', 'currency_source', 'amount_destination', 'amount_estimated', 'currency_destination'
            ]);

        $updated_transaction = \App\Transaction::where('id', $transaction->id)->first();
        self::assertEquals(51, $updated_transaction->frequency);
    }
}
