<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Account;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::table('accounts')->delete();

        $accounts = [
            ["number" => "123456789",
                "alias" => "alias1",
                "linked" => 0,
                "currency" => "EUR",
                "amount" => 10000,
                "user_id" => 1
                ],
            ["number" => "789456123",
                "linked" => 1,
                "currency" => "EUR",
                "amount" => 25000,
                "user_id" => 1
            ],
            ["number" => "ES15 2470 4447 8888",
                "linked" => 1,
                "currency" => "EUR",
                "amount" => 2377.15,
                "user_id" => 1
            ]
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }
        Model::reguard();
    }
}
