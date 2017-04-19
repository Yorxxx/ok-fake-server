<?php

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TransactionsTransformerTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Given a transaction, the transformer should map into expected output data
     */
    public function given_transaction_when_transform_Then_ReturnsCorrectFormatedJsonData()
    {

        $transformer = new \App\Transformers\TransactionsTranformer;

        $source_user = factory(App\User::class)->create([]);
        $source_account = factory(\App\Account::class)->create([
            'user_id'   => $source_user->id,
            'number'    => "7777"
        ]);
        $dest_user = factory(App\User::class)->create([]);

        $time = new DateTime('now');
        $expectedTime = $time->getTimestamp()*1000; // Expected output in milliseconds

        $dest_agent = factory(App\Agent::class)->create([
            'user_id'   => $dest_user->id,
            'name'      => "Foo Bar",
            'phone'     => "+44-123456789",
            'account'   => "5555",
            'country'   => "ES"
        ]);

        $transaction = factory(App\Transaction::class)->create([
            'account_source'        => $source_account->id,
            'agent_destination'     => $dest_agent->id,
            'user_id'               => $source_user->id,
            'amount_destination'    => 10000,
            'amount_source'         => 10001,
            'state'                 => 5,
            'concept'               => "concepto",
            'currency_destination'  => "EUR",
            'date_start'            => $time,
            'date_end'              => $time,
            'date_creation'         => $time
        ]);

        $result = $transformer->transform($transaction);

        // Assert
        self::assertNotNull($result);
        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('agent_destination', $result);
        self::assertArrayHasKey('date_start', $result);
        self::assertArrayHasKey('date_end', $result);
        self::assertArrayHasKey('date_creation', $result);
        self::assertArrayHasKey('amount_destination', $result);
        self::assertArrayHasKey('amount_estimated', $result);
        self::assertArrayHasKey('state', $result);
        self::assertArrayHasKey('concept', $result);
        self::assertArrayHasKey('currency_destination', $result);
        self::assertArrayHasKey('amount_source', $result);
        self::assertArrayHasKey('currency_source', $result);
        self::assertArrayHasKey('agent_destination', $result);
        self::assertArrayHasKey('agent_source', $result);

        self::assertEquals($transaction->id, $result['id']);
        self::assertNotNull($result['date_start']);
        self::assertNotNull($result['date_end']);
        self::assertNotNull($result['date_creation']);
        self::assertEquals(10000, $result['amount_destination']);
        self::assertEquals(10001, $result['amount_estimated']);
        self::assertEquals(5, $result['state']);
        self::assertEquals("concepto", $result['concept']);
        self::assertEquals("EUR", $result['currency_destination']);
        self::assertEquals($expectedTime, $result['date_start']);
        self::assertEquals($expectedTime, $result['date_end']);
        self::assertEquals($expectedTime, $result['date_creation']);

        $result_dest_agent = $result['agent_destination'];
        self::assertNotNull($result_dest_agent);
        self::assertArrayHasKey('id', $result_dest_agent);
        self::assertArrayHasKey('name', $result_dest_agent);
        self::assertArrayHasKey('phone', $result_dest_agent);
        self::assertArrayHasKey('prefix', $result_dest_agent);
        self::assertArrayHasKey('account', $result_dest_agent);
        self::assertArrayHasKey('country', $result_dest_agent);
        self::assertArrayHasKey('sort_code', $result_dest_agent);
        self::assertEquals("Foo Bar", $result_dest_agent['name']);
        self::assertEquals("5555", $result_dest_agent['account']);
        self::assertEquals("ES", $result_dest_agent['country']);
        self::assertSame("44", $result_dest_agent['prefix']);

        $result_source_agent = $result['agent_source'];
        self::assertNotNull($result_source_agent);
        self::assertArrayHasKey('account', $result_source_agent);
        self::assertEquals("7777", $result_source_agent['account']);
    }

    /**
     * @test
     * Mapping from null, returns null
     */
    public function given_nullValues_When_mapFromRequest_Then_ReturnsNull() {

        $transformer = new \App\Transformers\TransactionsTranformer;

        // Act
        $result = $transformer->mapFromRequest(null);

        // Assert
        self::assertNull($result);
    }

    /**
     * @test
     * Mapping from valid input data, should return correct transaction values
     */
    public function given_inputValues_When_MapFromRequest_Then_ReturnsCorrectTransactionData() {

        $transformer = new \App\Transformers\TransactionsTranformer;

        $values = [
            'emisor_account'            => 1,
            'agent_destination'         => 10,
            'concept'                   => 'foo',
            'amount'                    => 50,
            'amount_estimated'          => "42.5",
            'currency_source'           => 'EUR',
            'currency_destination'      => 'EUR'
        ];

        // Act
        $result = $transformer->mapFromRequest($values);

        // Assert
        self::assertNotNull($result);
        self::assertArrayHasKey('concept', $result);
        self::assertArrayHasKey('amount_source', $result);
        self::assertArrayHasKey('amount_destination', $result);
        self::assertArrayHasKey('currency_source', $result);
        self::assertArrayHasKey('currency_destination', $result);
        self::assertArrayHasKey('state', $result);
        self::assertArrayHasKey('frequency', $result);
        self::assertArrayHasKey('sms_custom_text', $result);
        self::assertArrayHasKey('agent_destination', $result);
        self::assertArrayHasKey('agent_source', $result);
        self::assertArrayHasKey('user_id', $result);

        self::assertEquals('foo', $result['concept']);
        self::assertEquals(null, $result['agent_source']);
        self::assertEquals(10, $result['agent_destination']);
        self::assertEquals(50, $result['amount_source']);
        self::assertEquals(42.5, $result['amount_destination']);
        self::assertEquals("EUR", $result['currency_source']);
        self::assertEquals("EUR", $result['currency_destination']);
        self::assertEquals(0, $result['state']);
        self::assertEquals(1, $result['frequency']);
    }
}
