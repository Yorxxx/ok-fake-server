<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::table('settings')->delete();

        // Retrieve all users, and for each one, generate a random amount of settings
        $users = User::all();

        foreach ($users as $user) {
            $max = random_int(1, 5);
            for ($i = 0; $i< $max; $i++) {
                factory(App\Setting::class)->create([
                    'user_id' => $user->id
                ]);
            }
        }

        Model::reguard();
    }
}
