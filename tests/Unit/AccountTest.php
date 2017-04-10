<?php

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AccountTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Given an account, should return related user
     */
    public function given_account_when_user_Then_ReturnsUserAccount() {
        $user = factory(App\User::class)->create(['name'  => 'John Doe']);
        $account = factory(App\Account::class)->create([
            'user_id'   => $user->id
        ]);

        // Act
        $result = $account->user;

        // Assert
        self::assertNotNull($result);
        self::assertEquals($user->id, $result->id);
    }
}
