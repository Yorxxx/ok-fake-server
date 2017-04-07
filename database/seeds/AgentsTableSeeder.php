<?php

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class AgentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::table('agents')->delete();

        // Retrieve all users, and for each one, generate a random amount of agents
        $users = User::all();

        foreach ($users as $user) {
            $max = random_int(0, 25);
            for ($i = 0; $i< $max; $i++) {
                factory(App\Agent::class)->create([
                    'user_id' => $user->id
                ]);
            }
        }

        Model::reguard();
    }
}
