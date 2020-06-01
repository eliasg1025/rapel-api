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
    $router->get('/data/por-empresa', 'DataController@porEmpresa');
    $router->get('/data/localidades', 'DataController@localidades');
    $router->get('/trabajador/{id_empresa}/{dni}', 'TrabajadoresController@show');
    $router->get('/trabajador/{id_empresa}/{dni}/info', 'TrabajadoresController@info');
    $router->get('/departamento', 'DepartamentosController@get');
    $router->get('/departamento/{codigo}', 'DepartamentosController@show');
    $router->get('/departamento/{codigo}/provincias', 'DepartamentosController@provincias');
    $router->get('/provincia/{codigo}', 'ProvinciasController@show');
    $router->get('/provincia/{codigo}/distritos', 'ProvinciasController@distritos');
    $router->get('/distrito/{codigo}', 'DistritosController@show');
    $router->get('/tipo-zona/{id_empresa}', 'TipoZonaController@get');
    $router->get('/tipo-via/{id_empresa}', 'TipoViaController@get');
    $router->get('/nacionalidad/{id_empresa}', 'NacionalidadController@get');
    $router->get('/nacionalidad/{id_empresa}/{id_nacionalidad}', 'NacionalidadController@show');
    $router->get('/nivel_educativo/{id_empresa}', 'NivelEducativoController@get');
    $router->get('/nivel_educativo/{id_empresa}/{id_nivel_educativo}', 'NivelEducativoController@show');
    $router->get('/troncal/{id_empresa}', 'TroncalController@get');
    $router->get('/troncal/{id_empresa}/{codigo}', 'TroncalController@show');
    $router->get('/troncal/{id_empresa}/{codigo}/rutas', 'TroncalController@rutas');
    $router->get('/ruta/{id_empresa}/{codigo_troncal}/{codigo_ruta}', 'RutaController@show');
    $router->get('/zona-labor/{id_empresa}', 'ZonaLaborController@get');
});
