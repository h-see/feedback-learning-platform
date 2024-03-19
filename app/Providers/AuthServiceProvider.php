<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('isAdmin', 'App\Policies\RolePolicy@isAdmin');
        Gate::define('isTeacher', 'App\Policies\RolePolicy@isTeacher');

        Gate::define('userIsCounsellingCreator', 'App\Policies\CounsellingPolicy@userIsCounsellingCreator');
        Gate::define('userAlreadySubmittedFeedback', 'App\Policies\FeedbackPolicy@userAlreadySubmittedFeedback');
        Gate::define('userIsLockedPeerReviewer', 'App\Policies\FeedbackPolicy@userIsLockedPeerReviewer');
    }
}
