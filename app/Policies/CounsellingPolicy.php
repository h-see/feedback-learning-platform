<?php

namespace App\Policies;

use App\Models\User;
use App\Counselling\Models\Counselling;

class CounsellingPolicy
{
    /** checks if current user is creator of the given counselling
     *
     * @param  \App\Models\User  $user
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return bool
     */
    public function userIsCounsellingCreator(User $user, Counselling $counselling) {
        return $user->id === $counselling->courseMember->user_id;
    }
}
