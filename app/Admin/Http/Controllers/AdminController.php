<?php

namespace App\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    /**
     * Show home view for admin
     */
    public function index()
    {
        return view('admin.index');
    }
}
