<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'document' => random_int(30000000, 60000000) . ucfirst($faker->randomLetter),
        'doctype' => 'N',
        'phone' => '+34-' . $faker->phoneNumber
    ];
});

$factory->define(App\Account::class, function (Faker\Generator $faker) {

    return [
        'number' => $faker->unique()->bankAccountNumber,
        'alias' => $faker->word,
        'linked' => random_int(0, 1),
        'currency' => 'EUR',
        'amount' => $faker->randomFloat(2, 100, 1000000),
        'user_id' => function() {
        // Create a new user with every new account
            return factory(App\User::class)->create()->id;
        }
    ];
});