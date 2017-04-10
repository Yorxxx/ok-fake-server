<?php

use App\Transformers\AccountsTranformer;
use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AgentTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Given an account, the transformer should map into expected output data
     */
    public function given_account_when_transform_Then_ReturnsCorrectFormatedJsonData()
    {

        $transformer = new AccountsTranformer;

        $account = factory(App\Account::class)->create([
            'number' => "10000",
            'linked' => false,
            'currency' => 'FOO',
            'amount' => 10487.55,
            'user_id' => 100,
            'alias' => 'account_alias'
        ]);

        $result = $transformer->transform($account);

        // Assert
        self::assertNotNull($result);
        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('number', $result);
        self::assertArrayHasKey('linked', $result);
        self::assertArrayHasKey('currency', $result);
        self::assertArrayHasKey('amount', $result);
        self::assertArrayHasKey('user_id', $result);
        self::assertArrayHasKey('alias', $result);
        self::assertEquals($account->id, $result['id']);
        self::assertEquals(10000, $result['number']);
        self::assertEquals(false, $result['linked']);
        self::assertEquals("FOO", $result['currency']);
        self::assertEquals(10487.55, $result['amount']);
        self::assertEquals(100, $result['user_id']);
        self::assertEquals("account_alias", $result['alias']);
    }
}
