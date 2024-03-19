<?php

namespace App\Counselling\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class CounsellingServiceProvider extends ServiceProvider {

    public function boot(Router $router) {
        $this->registerRoutes($router);
    }

    protected function registerRoutes(Router $router) {
        $router->group(
            ['namespace' => 'App\Counselling\Http\Controllers', 'prefix' => 'counselling', 'middleware' => ['web', 'auth']],
            function ($router) {
                $router->get('/{counselling}', [
                    'as' => 'counselling.index',
                    'uses' => 'CounsellingController@index'
                ]);

                $router->get('/{counselling}/feedback/{counsellingFeedback}', [
                    'as' => 'counselling.indexPeer',
                    'uses' => 'CounsellingController@indexPeer'
                ]);

                $router->get('/{counselling}/data', [
                    'as' => 'counselling.indexData',
                    'uses' => 'CounsellingController@indexData'
                ]);

                $router->get('/{counselling}/data/feedback/{counsellingFeedback}', [
                    'as' => 'counselling.indexDataPeer',
                    'uses' => 'CounsellingController@indexDataPeer'
                ]);

                $router->get('/{counselling}/feedback/{counsellingFeedback}/lock-peer', [
                    'as' => 'counselling.lockPeer',
                    'uses' => 'CounsellingController@lockPeer'
                ]);

                $router->get('/{counselling}/pseudonym', [
                    'as' => 'counselling.pseudonym',
                    'uses' => 'CounsellingController@getPseudonymForCounselling'
                ]);

                $router->get('/setup/{counsellingSetup}', [
                    'as' => 'counselling.setup.id',
                    'uses' => 'CounsellingController@getCounsellingBySetupId'
                ]);

                $router->get('/setup/course/{mandatory}/{course}', [
                    'as' => 'counselling.course.id',
                    'uses' => 'CounsellingController@getCounsellingByCourseId'
                ]);

                $router->get('/create/{counsellingSetup}', [
                    'as' => 'counselling.createView',
                    'uses' => 'CounsellingController@showWizard'
                ]);

                $router->post('/{counsellingSetup}', [
                    'as' => 'counselling.store',
                    'uses' => 'CounsellingController@store'
                ]);

                $router->put('/{counselling}/finish-chat', [
                    'as' => 'counselling.finishChat',
                    'uses' => 'CounsellingController@finishChat'
                ]);

                $router->put('/{counselling}/generate-ai-feedback', [
                    'as' => 'counselling.generateAIFeedback',
                    'uses' => 'CounsellingController@generateAIFeedback'
                ]);

                $router->delete('/{counselling}', [
                    'as' => 'counselling.delete',
                    'uses' => 'CounsellingController@delete'
                ]);

                $router->put('/{counselling}', [
                    'as' => 'counselling.update',
                    'uses' => 'CounsellingController@update'
                ]);

                $router->post('/{counselling}/message', [
                    'as' => 'counselling.message.store',
                    'uses' => 'CounsellingController@storeMessage'
                ]);

                $router->get('/{counselling}/message', [
                    'as' => 'counselling.message.generate',
                    'uses' => 'CounsellingController@generate'
                ]);

                $router->put('/addition/{counsellingId}/{messageNumber}', [
                    'as' => 'counselling.addition',
                    'uses' => 'CounsellingController@editMessageAddition'
                ]);

                $router->post('/{counselling}/request-feedback', [
                    'as' => 'counselling.feedback.storeRequest',
                    'uses' => 'CounsellingFeedbackController@storeRequest'
                ]);

                $router->post('/{counselling}/feedback/{feedbackRequest}/submit-feedback', [
                    'as' => 'counselling.feedback.storeFeedback',
                    'uses' => 'CounsellingFeedbackController@storeFeedback'
                ]);

                $router->post('/{counselling}/feedback/{feedbackRequest}/auto-save', [
                    'as' => 'counselling.feedback.auto-save',
                    'uses' => 'CounsellingFeedbackController@autoSave'
                ]);

                $router->post('/{counselling}/feedback/{feedbackRequest}/cancel', [
                    'as' => 'counselling.feedback.cancelStoreFeedback',
                    'uses' => 'CounsellingFeedbackController@cancelStoreFeedback'
                ]);

                $router->post('/{counselling}/feedback/{feedbackRequest}/update-newAvailable', [
                    'as' => 'counselling.feedbackRequestUpdateNewAvailable',
                    'uses' => 'CounsellingFeedbackController@updateNewAvailable'
                ]);
            }
        );
    }
}
