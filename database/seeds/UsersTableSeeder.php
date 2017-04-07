<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();

        for ($i = 0; $i < 10; $i++) {
            // Add random users
            factory(App\User::class)->create();
        }

        Model::reguard();
    }
}
