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
        DB::table('transactions')->delete();

        // Retrieve all users, and for each one, generate a random amount of transactions
        $users = User::all();

        foreach ($users as $user) {
            // For every agent of the user, we add a transaction
            $agents = $user->agents;
            foreach ($agents as $agent) {
                factory(App\Transaction::class)->create([
                    'user_id'           =>  $user->id,
                    'agent_source'      =>  $user->id,
                    'agent_destination' =>  $agent->id
                ]);
            }
        }

        Model::reguard();
    }
}
