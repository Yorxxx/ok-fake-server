<?php

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Retrieve all accounts, and for each one, generate a random amount of transactions
        $accounts = \App\Account::all();

        foreach ($accounts as $account) {
            $user = $account->user;
            $agents = $user->agents;
            foreach ($agents as $agent) {
                factory(App\Transaction::class)->create([
                    'user_id'           =>  $user->id,
                    'account_source'    =>  $account->id,
                    'agent_destination' =>  $agent->id
                ]);
            }
        }

        Model::reguard();
    }
}
