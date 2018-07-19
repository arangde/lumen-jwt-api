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

$router->post(
    'auth/login', 
    [
       'uses' => 'AuthController@authenticate'
    ]
);

$router->post(
    'admin/login', 
    [
       'uses' => 'AdminController@authenticate'
    ]
);

$router->group(['middleware' => 'jwt'], function() use ($router) {
    
    $router->get('profile', 'MemberController@getProfile');
    $router->post('profile', 'MemberController@saveProfile');

    $router->group(['middleware' => 'checkAdmin'], function() use ($router) {
        $router->get('users', 'UserController@getUsers');
        
    });
});