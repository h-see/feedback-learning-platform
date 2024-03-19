<?php

namespace App\Course\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class CoursesServiceProvider extends ServiceProvider {

    public function boot(Router $router) {
        $this->registerRoutes($router);
    }

    protected function registerRoutes(Router $router) {
        $router->group(
            ['namespace' => 'App\Course\Http\Controllers', 'prefix' => 'courses'],
            function ($router) {

            // editingteacher-routes
            $router->group(['middleware' => ['web', 'auth', 'editingteacher']], function ($router) {
                $router->get('/created', [
                    'as' => 'courses.created',
                    'uses' => 'CoursesController@getCreatedCourses'
                ]);
            });

            $router->group(['middleware' => ['web', 'auth']], function ($router) {
                $router->get('/', [
                    'as' => 'courses.list',
                    'uses' => 'CoursesController@getCourses'
                ]);

                $router->get('/enrollment', [
                    'as' => 'courses.enrollment',
                    'uses' => 'CoursesController@enrollmentView'
                ]);

                $router->get('/trainer-enrollment', [
                    'as' => 'courses.trainerEnrollment',
                    'uses' => 'CoursesController@trainerEnrollmentView'
                ]);
            });
        });
    }

}
