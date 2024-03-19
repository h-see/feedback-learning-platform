<?php

namespace App\Persona\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class CounsellingFieldsServiceProvider extends ServiceProvider {

    public function boot(Router $router) {
        $this->registerRoutes($router);
    }

    protected function registerRoutes(Router $router) {
        $router->group(
            ['namespace' => 'App\Persona\Http\Controllers', 'prefix' => 'counselling-fields'],
            function ($router) {

            // admin-routes
            $router->group(['middleware' => ['web', 'auth', 'admin']], function ($router) {
                $router->put('/{counsellingField}', [
                    'as' => 'counsellingField.store',
                    'uses' => 'CounsellingFieldsController@update'
                ]);
            });
    
        });
    }

}