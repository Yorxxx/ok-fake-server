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
        DB::table('users')->delete();

        for ($i = 0; $i < 10; $i++) {
            // Add random users
            factory(App\User::class)->create([
                'document' => random_int(30000000, 60000000) . ucfirst(str_random(1)),
                'doctype' => 'N',
                'phone' => '+34-' . random_int(600000000, 699999999),
                'password' => bcrypt('foo')]);
        }

        // Add our desired users
        factory(App\User::class)->create([
                'name' => 'Jorge García',
                'email' => 'jorgegarcia.sopra@gmail.com',
                'password' => bcrypt('foo'),
                'doctype' => 'N',
                'document' => '44878587K',
                'phone' => '+34-646547055']
        );

        Model::reguard();
    }
}
