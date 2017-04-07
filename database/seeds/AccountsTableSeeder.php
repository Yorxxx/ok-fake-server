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
        DB::table('users')->delete();

        for ($i = 0; $i < 10; $i++) {
            // Add random accounts
            factory(App\Account::class)->create();
        }

        // Add our desired users
        $user = factory(App\User::class)->create([
                    'name' => 'Jorge García',
                    'email' => 'jorgegarcia.sopra@gmail.com',
                    'password' => bcrypt('5780'),
                    'doctype' => 'N',
                    'document' => '44878587K',
                    'phone' => '+34-646547055']
            );

        factory(App\Account::class)->create([
            'alias' => 'cuenta principal',
            'currency' => 'EUR',
            'amount' => 87549,
            'user_id' => $user->id
        ]);
        factory(App\Account::class)->create([
            'alias' => 'cuenta secundaria',
            'currency' => 'GBP',
            'amount' => 12500,
            'user_id' => $user->id
        ]);


        Model::reguard();
    }
}
