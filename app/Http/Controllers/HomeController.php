<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();
            session()->forget('current_course');
            if ($user->role->title === 'admin') {
                return view('admin.index');
            } else if ($user->role->title === 'editingteacher') {
                return view('teacher.index');
            }else if ($user->role->title === 'teacher') {
                return view('teacher.nonedit-index');
            }else {
                return view('student.index');
            }
        }

        return view('home');
    }
}
