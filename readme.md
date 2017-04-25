OK-Fake-Server
===================
<p align="center">
<a href="https://travis-ci.org/Yorxxx/ok-fake-server.svg?branch=master"><img src="https://travis-ci.org/Yorxxx/ok-fake-server.svg?branch=master" alt="Continous Integration"></a>
<a href="https://codecov.io/gh/Yorxxx/ok-fake-server">
  <img src="https://codecov.io/gh/Yorxxx/ok-fake-server/branch/master/graph/badge.svg" alt="Codecov" />
</a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

Dingo/Laravel server to provide data to ok-p2p clients.
The main purpose of this server is to provide fake data and not be in need to use client server.

----------


## About Laravel

[Laravel](https://laravel.com/) is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, yet powerful, providing tools needed for large, robust applications. A superb combination of simplicity, elegance, and innovation give you tools you need to build any application with which you are tasked.

## About Dingo

The [Dingo API](https://github.com/dingo/api) package is meant to provide you, the developer, with a set of tools to help you easily and quickly build your own API. While the goal of this package is to remain as flexible as possible it still won't cover all situations and solve all problems.

This package provides tools for the following, and more:

- Content Negotiation
- Multiple Authentication Adapters
- API Versioning
- Rate Limiting
- Response Transformers and Formatters
- Error and Exception Handling
- Internal Requests
- API Blueprint Documentation

## Requirements

- PHP 5.6+
- Composer
- PHP curl extension

## Installation

First install dependencies via composer.
```sh
$ composer install //Install dependencies
$ php artisan vendor:publish --provider="Dingo\Api\Provider\LaravelServiceProvider" //Add Dingo provider
$ php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider" //Add JWTAuth for authentication
$ php artisan jwt:generate //Generate a key
```


## First launch
There are a couple of steps to do before executing for the first time.

#### Define endpoint
Define the endpoint of all the rest calls.
```sh
$ echo "API_PREFIX='api'" > .env
```
There are more environment variables . See [Dingo Configuration](https://github.com/dingo/api/wiki/Configuration) for more info.


#### Create database
You need to create a database with associated tables in order to query elements. Go to the project folder and execute the following

```sh
$ touch database/database.sqlite // Create an empty file
$ php artisan migrate //This will execute the migration files
```

Now the database is created, but it is empty.

#### Add data
Since the application does not allow to add users or accounts, you should store the data manually on the database.
The first option is to run the database seeds provided with the project. This will add users, accounts, transactions and contacts.

```sh
$ php artisan migrate
```
Users would be created with a default password of 0000. You might need to check user's document value, though.

Another option, is to run an artisan command for adding users. This way, you have control over required user info, like name and login credentials.

The execution script will ask for some input. When it has finished, an output will be displayed in the screen, with info about user, his account and performed transactions.
```sh
$ php artisan user:add
 Specify user name:
 > Foo Bar
 Specify user NIF?:
 > 11223344A
 Specify user password:
 >

 This will create a user with fake account, contacts and transactions. Proceed? (yes/no) [no]:
 > yes

Feeding data
 10/10 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
 +---------+-------------+-----------+---------+-------------------------+
| User ID | Name        | Document  | DocType | Email                    |
+---------+-------------+-----------+---------+--------------------------+
| 3       | Foo Bar     | 11223344A | N       | elody.durgan@example.org |
+---------+-------------+-----------+---------+--------------------------+

+------------+----------------------------------+-----------+----------+
| Account ID | Number                           | Amount    | Currency |
+------------+----------------------------------+-----------+----------+
| 3          | ES376508099128672015618101486163 | 321310.82 | EUR      |
+------------+----------------------------------+-----------+----------+

+------------+--------------------------+--------------+---------------+
| Contact ID | Name                     | Phone        | Country     |
+------------+--------------------------+--------------+-------------+
| 26         | Prof. Treva Baumbach DDS | 34-617280197 | Singapore   |
| 27         | Roma Ondricka PhD        | 34-643505558 | Georgia     |
| 28         | Vita Eichmann            | 34-603742780 | Estonia     |
| 29         | Felix Grady              | 34-673370724 | Iraq        |
| 30         | Rollin Veum MD           | 34-699204544 | Paraguay    |
| 31         | Lempi Considine          | 34-613669767 | Estonia     |
| 32         | Brielle Franecki         | 34-627955125 | Timor-Leste |
+------------+--------------------------+--------------+-------------+

+----------------+----------+---------+-------+-------------------+
| Transaction ID | Concept  | Amount  | State | Agent destination |
+----------------+----------+---------+-------+-------------------+
| 27             | dolores  | 415.344 | 8     | 26                |
| 28             | suscipit | 193.669 | 0     | 27                |
| 29             | est      | 319.185 | 0     | 28                |
| 30             | non      | 264.162 | 3     | 29                |
| 31             | dolor    | 343.67  | 6     | 30                |
| 32             | ratione  | 53.23   | 7     | 31                |
| 33             | aut      | 14.783  | 4     | 32                |
+----------------+----------+---------+-------+-------------------+
```
#### Update transactions
A not required but recommended action is to mimic the transaction's state updates. After performing a new transaction in the app, it will be saved with an _in_process_ state. This is ok for demo purposes, but it could be even better if this "processing" transactions would automatically update to a definitive state after a while.
For this, an Scheduled task has been added, that will update every _processing_ transaction to _completed_ after a couple of hours.
You just need to add the following cron job to your machine.
```sh
$ php artisan schedule:run
```
This will execute every artisan command scheduled in the server.

If you'd like to execute the command manually:
```sh
$ php artisan transaction:update
```

## Execution
Now that you have configured the server for the first time, it can be launched:
```sh
$ php artisan serve
```
