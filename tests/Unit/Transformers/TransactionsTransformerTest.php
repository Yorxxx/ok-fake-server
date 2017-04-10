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
        $dest_user = factory(App\User::class)->create([]);

        $dest_agent = factory(App\Agent::class)->create([
            'user_id'   => $dest_user->id,
            'name'      => "Foo Bar",
            'phone'     => "+44-123456789",
            'account'   => "5555",
            'country'   => "ES"
        ]);

        $transaction = factory(App\Transaction::class)->create([
            'agent_source'          => $dest_agent->account,
            'agent_destination'     => $dest_agent->id,
            'user_id'               => $source_user->id,
            'amount_destination'    => 10000,
            'amount_source'         => 10001,
            'state'                 => 5,
            'concept'               => "concepto",
            'currency_destination'  => "EUR"
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

        $result_source_agent = $result['agent_source'];
        self::assertNotNull($result_source_agent);
        self::assertArrayHasKey('account', $result_source_agent);
        self::assertEquals("5555", $result_source_agent['account']);
    }
}
