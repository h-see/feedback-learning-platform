<?php

namespace App\Persona\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class PersonasServiceProvider extends ServiceProvider {

    public function boot(Router $router) {
        $this->registerRoutes($router);
    }

    protected function registerRoutes(Router $router) {
        $router->group(
            ['namespace' => 'App\Persona\Http\Controllers', 'prefix' => 'personas'],
            function ($router) {

            // admin-routes
            $router->group(['middleware' => ['web', 'auth', 'admin']], function ($router) {
                $router->put('/{persona}', [
                    'as' => 'personas.store',
                    'uses' => 'PersonasController@update'
                ]);
            });
    
            $router->group(['middleware' => ['web', 'auth', 'editingteacher']], function ($router) {
                $router->get('/', [
                    'as' => 'personas.index',
                    'uses' => 'PersonasController@index'
                ]);
            });
        }); 
    }
}