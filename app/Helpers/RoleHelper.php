<?php

namespace App\Helpers;

use App\Models\Role;

class RoleHelper
{
    /**
     * Transfom role_id in german string for displaying
     */
    public static function translateRole($role_id)
    {
        switch (RoleHelper::getRoleById($role_id)) {
            case 'admin':
                return 'Administrator';
            case 'editingteacher':
                return 'Trainer*in mit Bearbeitungsrecht';
            case 'teacher':
                return 'Trainer*in ohne Bearbeitungsrecht';
            case 'student':
                return 'Kursteilnehmer*in';
            default:
                return '';
        }
    }

    /**
     * returns role->title for given id
     */
    public static function getRoleById($role_id) {
        $role = Role::find($role_id);
        return $role ? $role->title : null;
    }

    /**
     * returns role->id for given title
     */
    public static function getIdFromTitle($title) {
        $role = Role::where('title', $title)->first();
        return $role ? $role->id : null;
    }
}
