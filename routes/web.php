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
    
    $router->get('withdrawals/{id}', ['uses' => 'WithdrawalController@get']);
    $router->get('redeems/{id}', ['uses' => 'RedeemController@get']);
    $router->get('sales/{id}', ['uses' => 'SaleController@get']);
    $router->get('incomes/{id}', ['uses' => 'IncomeController@get']);
    $router->get('points/{id}', ['uses' => 'PointController@get']);

    $router->get('announcements', ['uses' => 'AnnouncementController@index']);
    $router->put('announcements/{id}/read', ['uses' => 'AnnouncementController@read']);
    $router->post('announcements/read', ['uses' => 'AnnouncementController@mutiread']);

    $router->group(['middleware' => 'checkMember'], function() use ($router) {
        $router->get('profile', 'MemberController@getProfile');
        $router->put('profile', 'MemberController@saveProfile');

        $router->post('withdrawals', ['uses' => 'WithdrawalController@create']);
        $router->post('redeems', ['uses' => 'RedeemController@create']);
    });
    
    $router->group(['middleware' => 'checkAdmin'], function() use ($router) {
        $router->get('dashboard', ['uses' => 'AdminController@getDashboard']);
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
        $router->get('members/{id}/incomes', ['uses' => 'MemberController@getIncomes']);
        $router->get('members/{id}/withdrawals', ['uses' => 'MemberController@getWithdrawals']);
        $router->get('members/{id}/redeems', ['uses' => 'MemberController@getRedeems']);
        $router->get('members/{id}/points', ['uses' => 'MemberController@getPoints']);
        $router->get('members/{id}/sales', ['uses' => 'MemberController@getSales']);
        $router->get('members/{id}/refers', ['uses' => 'MemberController@getRefers']);
        $router->post('members/register', ['uses' => 'MemberController@createManual']);

        $router->get('incomes', ['uses' => 'IncomeController@index']);

        $router->get('points', ['uses' => 'PointController@index']);

        $router->get('withdrawals', ['uses' => 'WithdrawalController@index']);
        $router->put('withdrawals/{id}', ['uses' => 'WithdrawalController@update']);
        $router->post('withdrawals/{id}/accept', ['uses' => 'WithdrawalController@accept']);
        $router->post('withdrawals/{id}/reject', ['uses' => 'WithdrawalController@reject']);
        $router->delete('withdrawals/{id}', ['uses' => 'WithdrawalController@delete']);

        $router->get('redeems', ['uses' => 'RedeemController@index']);
        $router->post('redeems/{id}/accept', ['uses' => 'RedeemController@accept']);
        $router->post('redeems/{id}/reject', ['uses' => 'RedeemController@reject']);

        $router->get('sales', ['uses' => 'SaleController@index']);
        $router->post('sales', ['uses' => 'SaleController@create']);
        $router->put('sales/{id}', ['uses' => 'SaleController@update']);
        $router->delete('sales/{id}', ['uses' => 'SaleController@delete']);

        $router->get('settings', ['uses' => 'SettingController@index']);
        $router->post('settings', ['uses' => 'SettingController@create']);
        $router->put('settings', ['uses' => 'SettingController@update']);
        $router->delete('settings/{id}', ['uses' => 'SettingController@delete']);

        $router->post('announcements', ['uses' => 'AnnouncementController@create']);
        $router->get('announcements/{id}', ['uses' => 'AnnouncementController@get']);
        $router->put('announcements/{id}', ['uses' => 'AnnouncementController@update']);
        $router->delete('announcements/{id}', ['uses' => 'AnnouncementController@delete']);
    });
});