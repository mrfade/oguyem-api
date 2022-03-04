<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/menus', ['uses' => 'MenuController@showMenu']);
$router->get('/menus/{date}', ['uses' => 'MenuController@showDateMenu']);

$router->get('/menus/{date}/comments', ['middleware' => 'auth', 'uses' => 'MenuController@showComments']);
$router->get('/menus/{date}/comments/{id}', ['middleware' => 'auth', 'uses' => 'MenuController@showComment']);
$router->post('/menus/{date}/comments', ['middleware' => 'auth', 'uses' => 'MenuController@newComment']);
$router->delete('/menus/{date}/comments/{id}', ['middleware' => 'auth', 'uses' => 'MenuController@deleteComment']);
$router->post('/menus/{date}/comments/{id}/vote', ['middleware' => 'auth', 'uses' => 'MenuController@voteComment']);

$router->post('/devices/register', ['uses' => 'UserController@register']);

$router->get('/cron/fetch-menus', ['uses' => 'CronController@fetchMenus']);
