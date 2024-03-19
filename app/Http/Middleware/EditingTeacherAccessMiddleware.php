<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EditingTeacherAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && (Auth::user()->role->title === 'editingteacher' || Auth::user()->role->title === 'admin')) {
            return $next($request);
        }
        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        } else {
            return redirect('/');
        }
    }
}
