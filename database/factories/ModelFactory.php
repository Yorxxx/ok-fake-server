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
use App\Account;
use App\Agent;
use App\User;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('1111'),
        'remember_token' => str_random(10),
        'document' => random_int(30000000, 60000000) . ucfirst($faker->randomLetter),
        'doctype' => 'N',
        'phone' => '+34-' . $faker->phoneNumber
    ];
});

$factory->define(App\Account::class, function (Faker\Generator $faker) {

    return [
        'number' => $faker->iban('ES') . $faker->unique()->bankAccountNumber,
        'alias' => $faker->word,
        'linked' => 1,
        'currency' => 'EUR',
        'amount' => $faker->randomFloat(2, 100, 1000000),
        'enterprise' => $faker->company,
        'contract_number' => ''  . $faker-> randomDigitNotNull,
        'user_id' => function() {
        // Create a new user with every new account
            return factory(App\User::class)->create()->id;
        }
    ];
});


$factory->define(App\Setting::class, function (Faker\Generator $faker) {

    $available_languages = ["es_ES", "en_GB", "fr_FR", "de_DE"];
    $random_language = array_values($available_languages)[random_int(0, count($available_languages)-1)];
    return [
        'language' => $random_language,
        'email_notifications' => random_int(0, 1),
        'sms_notifications' => random_int(0, 1),
        'app_notifications' => random_int(0, 1),
        'user_id' => function() {
            // Get a random user to work with
            $user = User::inRandomOrder()->first();
            return $user->id;
        }
    ];
});

$factory->define(App\Agent::class, function(\Faker\Generator $faker) {

    return [
        'account' => $faker->iban('ES') . $faker->bankAccountNumber,
        'owner' => 0,
        'name' => $faker->name,
        'phone' => '34-' . random_int(600000000, 700000000),
        'email' => $faker->unique()->safeEmail,
        'country' => $faker->country,
        'user_id' => function() {
            // Get a random user to work with
            $user = User::inRandomOrder()->first();
            return $user->id;
        }
    ];
});

$factory->define(App\Transaction::class, function(\Faker\Generator $faker) {

    $date = $faker->dateTimeThisMonth;

    $source = Account::inRandomOrder()->first();

    return [
        'concept' => $faker->name,
        'amount_source' => $faker->randomFloat(3, 10, 500),
        'amount_destination' => $faker->randomFloat(3, 10, 500),
        'currency_source' => 'EUR',
        'currency_destination' => 'EUR',
        'state' => random_int(0, 8),
        'frequency' => random_int(0, 10),
        'sms_custom_text' => $faker->sentence,
        'agent_destination' =>  function() {
            // Get a random user to work with
            $user = Agent::inRandomOrder()->first();
            return $user->id;
        },
        'account_source' =>  $source->id,
        'user_id' =>  $source->user->id,
        'date_creation' => $date,
        'date_start' => $date->add(date_interval_create_from_date_string('2 days')),
        'date_end' => $date->add(date_interval_create_from_date_string('5 days'))
    ];
});