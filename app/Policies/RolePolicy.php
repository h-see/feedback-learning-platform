<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function isAdmin(User $user) {
        return $user->role->title === 'admin';
    }

    public function isTeacher(User $user) {
        if($user->role->title === 'teacher' || $user->role->title === 'editingteacher'){
            return true;
        }
        return false;
    }
}
