<?php

namespace App\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class AdminServiceProvider extends ServiceProvider {

    public function boot(Router $router) {
        $this->registerRoutes($router);
    }

    protected function registerRoutes(Router $router) {

        $router->group(
            ['namespace' => 'App\Admin\Http\Controllers', 'middleware' => ['web', 'auth', 'admin']],
            function ($router) {
                $router->group(['prefix' => 'admin'], function ($router) {
                    $router->get('/', [
                        'as' => 'admin.index',
                        'uses' => 'AdminController@index'
                    ]);
    
                    $router->get('/user-management', [
                        'as' => 'admin.usermanagement',
                        'uses' => 'UserController@index'
                    ]);

                    $router->get('/roles', [
                        'as' => 'admin.roles',
                        'uses' => 'UserController@getRoles'
                    ]);
                });

                $router->group(['prefix' => 'users'], function ($router) {

                    $router->get('/', [
                        'as' => 'admin.users',
                        'uses' => 'UserController@indexData'
                    ]);
    
                    $router->put('/{user}/role', [
                        'uses' => 'UserController@updateUserRole'
                    ]);
    
                    $router->delete('/{user}', [
                        'uses' => 'UserController@delete'
                    ]);
    
                });
            }

        );
    }

}