<?php

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
    $api->post('login', 'App\Http\Controllers\AuthenticateController@authenticate');
    $api->get('currency', 'App\Http\Controllers\CurrenciesController@getCurrency');
});

$api->version('v1', ['middleware' => 'api.auth'], function ($api) {
    $api->get('users/me', 'App\Http\Controllers\AuthenticateController@getAuthenticatedUser');
    $api->get('accounts', 'App\Http\Controllers\AccountsController@getAccounts');
    $api->post('accounts/{id}/link', 'App\Http\Controllers\AccountsController@link');
    $api->post('accounts/{id}/unlink', 'App\Http\Controllers\AccountsController@unlink');
    $api->post('accounts/by_number', 'App\Http\Controllers\AccountsController@show');

    $api->get('settings', 'App\Http\Controllers\SettingsController@getSettings');
    $api->get('agents', 'App\Http\Controllers\AgentsController@getAgents');
    $api->post('agents', 'App\Http\Controllers\AgentsController@store');
    $api->put('agents/{id}', 'App\Http\Controllers\AgentsController@update');

    $api->get('transactions', 'App\Http\Controllers\TransactionsController@getTransactions');
    $api->post('transactions', 'App\Http\Controllers\TransactionsController@store');
    $api->get('transactions/{id}', 'App\Http\Controllers\TransactionsController@show');
    $api->get('transactions/{id}/signature_positions', 'App\Http\Controllers\TransactionsController@signaturePositions');
    $api->post('transactions/{id}/signature_otp', 'App\Http\Controllers\TransactionsController@signatureOtp');
    $api->post('transactions/{id}/signature_confirmation', 'App\Http\Controllers\TransactionsController@signatureConfirmation');
    $api->post('transactions/{id}/check_currency', 'App\Http\Controllers\TransactionsController@check_currency');
    $api->post('transactions/{id}/increase_frequency', 'App\Http\Controllers\TransactionsController@increase_frequency');
});