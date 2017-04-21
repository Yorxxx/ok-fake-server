<?php

use App\User;
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
        $users = User::all();

        foreach ($users as $user) {
            if (count($user->accounts) == 0) {
                factory(App\Account::class)->create([
                    'user_id' => $user->id
                ]);
            }
        }

        Model::reguard();
    }
}
