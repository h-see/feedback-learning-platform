<?php

namespace App\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Helpers\RoleHelper;
use App\Http\Controllers\Controller;

class UserController extends Controller
{   
    /** Show user management view for administrators
     */
    public function index()
    {
        return view('admin.users');
    }

    /** Get all users from database
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData() {
        $users = User::all()
            ->map(function($user) {
                $user->roleTitle = RoleHelper::translateRole($user->main_role_id);
                return $user;
            });

        return response()->json($users);
    }

    /** Update role of the given user
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function updateUserRole(Request $request, User $user) {
        $this->validate($request, [
            'role_id'     => 'required|exists:roles,id',
        ]);

        $new_role_id = $request->input('role_id');

        //ensure that there's always at least one admin
        if ($user->role->title === 'admin' && RoleHelper::getRoleById($new_role_id) !== 'admin' && $this->onlyOneAdmin()) {
            return response()->json(['message' => 'Mindestens ein Admin notwendig.'], 400);
        }
        
        // update the user's role
        $updateResult = $user->update(['main_role_id' => $new_role_id]);
        $user->roleTitle = RoleHelper::translateRole($user->main_role_id);
        
        if ($updateResult) {
            return response()->json(['message' => 'Benutzer aktualisiert', 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'Fehler beim Aktualisieren der Rechte'], 500);
        }
    }

    /** Delete the given user
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function delete(User $user) {
        //ensure that there's always at least one admin
        if ($user->role->title === 'admin' && $this->onlyOneAdmin()) {
            return response()->json(['message' => 'Mindestens ein Admin notwendig.'], 400);
        }

        $user->delete();
        return response()->json(['message' => 'Benutzer gelöscht'], 200);
    } 

    /** Get all possible roles from the database
     *
     * @return \Illuminate\Http\Response
     */
    public function getRoles() {
        $roles = Role::select('id', 'title')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($role) {
                $role->text = RoleHelper::translateRole($role->id);
                return $role;
            });
        return response()->json($roles);
    }

    /** Check if there is only one admin in the database
     *
     * @return bool
     */
    private function onlyOneAdmin() {
        $adminCount = User::whereHas('role', function ($query) {
            $query->where('title', 'admin');
        })->count();
        return $adminCount == 1;
    }
}
