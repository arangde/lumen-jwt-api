<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('login', ['uses' => 'AuthController@authenticate']);
$router->post('authorize', ['uses' => 'AuthController@checkToken']);

$router->post('admin/login', ['uses' => 'AdminController@authenticate']);
$router->post('admin/authorize', ['uses' => 'AdminController@checkToken']);

$router->group(['middleware' => 'jwt'], function() use ($router) {
    
    $router->get('profile', 'MemberController@getProfile');
    $router->put('profile', 'MemberController@saveProfile');

    $router->post('withdrawals', ['uses' => 'WithdrawalController@create']);
    $router->get('withdrawals/{id}', ['uses' => 'WithdrawalController@get']);

    $router->group(['middleware' => 'checkAdmin'], function() use ($router) {
        $router->get('users', ['uses' => 'UserController@index']);
        $router->post('users', ['uses' => 'UserController@create']);
        $router->get('users/{id}', ['uses' => 'UserController@get']);
        $router->put('users/{id}', ['uses' => 'UserController@update']);
        $router->delete('users/{id}', ['uses' => 'UserController@delete']);

        $router->get('members', ['uses' => 'MemberController@index']);
        $router->post('members', ['uses' => 'MemberController@create']);
        $router->get('members/{id}', ['uses' => 'MemberController@get']);
        $router->put('members/{id}', ['uses' => 'MemberController@update']);
        $router->delete('members/{id}', ['uses' => 'MemberController@delete']);
        $router->post('members/{id}/changePoint', ['uses' => 'MemberController@changePoint']);
        $router->get('members/{id}/incomes', ['uses' => 'MemberController@getIncomes']);
        $router->get('members/{id}/withdrawals', ['uses' => 'MemberController@getWithdrawals']);
        $router->get('members/{id}/points', ['uses' => 'MemberController@getPoints']);
        $router->get('members/{id}/sales', ['uses' => 'MemberController@getSales']);

        $router->get('withdrawals', ['uses' => 'WithdrawalController@index']);
        $router->put('withdrawals/{id}', ['uses' => 'WithdrawalController@update']);
        $router->post('withdrawals/{id}/accept', ['uses' => 'WithdrawalController@accept']);
        $router->post('withdrawals/{id}/reject', ['uses' => 'WithdrawalController@reject']);
        $router->delete('withdrawals/{id}', ['uses' => 'WithdrawalController@delete']);

        $router->get('sales', ['uses' => 'SaleController@index']);
        $router->post('sales', ['uses' => 'SaleController@create']);
        $router->get('sales/{id}', ['uses' => 'SaleController@get']);
        $router->put('sales/{id}', ['uses' => 'SaleController@update']);
        $router->delete('sales/{id}', ['uses' => 'SaleController@delete']);

        $router->get('settings', ['uses' => 'SettingController@index']);
        $router->post('settings', ['uses' => 'SettingController@create']);
        $router->put('settings', ['uses' => 'SettingController@update']);
        $router->delete('settings/{id}', ['uses' => 'SettingController@delete']);
    });
});