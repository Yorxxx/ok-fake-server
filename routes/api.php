<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->get('/', function() {
        return ['Fruits' => 'Delicious and healthy!'];
    });

    /*$api->get('fruits', 'App\Http\Controllers\FruitsController@index');
    $api->get('fruit/{id}', 'App\Http\Controllers\FruitsController@show');*/

    $api->post('authenticate', 'App\Http\Controllers\AuthenticateController@authenticate');
    $api->get('currency', 'App\Http\Controllers\CurrenciesController@getCurrency');
    /*$api->post('logout', 'App\Http\Controllers\AuthenticateController@logout');
    $api->get('token', 'App\Http\Controllers\AuthenticateController@getToken');*/

});

$api->version('v1', ['middleware' => 'api.auth'], function ($api) {
    $api->get('users/me', 'App\Http\Controllers\AuthenticateController@getAuthenticatedUser');
    $api->get('accounts', 'App\Http\Controllers\AccountsController@getAccounts');
    $api->get('settings', 'App\Http\Controllers\SettingsController@getSettings');
    $api->get('agents', 'App\Http\Controllers\AgentsController@getAgents');
    $api->post('agents', 'App\Http\Controllers\AgentsController@store');
    $api->get('transactions', 'App\Http\Controllers\TransactionsController@getTransactions');
    $api->get('transactions/{id}', 'App\Http\Controllers\TransactionsController@show');
});