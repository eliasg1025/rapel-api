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

$router->group(['prefix' => 'central'], function () use ($router) {
    $router->get('/trabajador/{id_empresa}/{dni}', 'TrabajadoresController@show');
    $router->get('/departamento', 'DepartamentosController@get');
    $router->get('/departamento/{codigo}', 'DepartamentosController@show');
    $router->get('/departamento/{codigo}/provincias', 'DepartamentosController@provincias');
    $router->get('/provincia/{codigo}', 'ProvinciasController@show');
    $router->get('/provincia/{codigo}/distritos', 'ProvinciasController@distritos');
    $router->get('/distrito/{codigo}', 'DistritosController@show');
});
