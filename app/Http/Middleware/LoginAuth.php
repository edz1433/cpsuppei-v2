<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LoginAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->check()){
            if(auth()->user()->hasRole('Supply Officer') || auth()->user()->hasRole('Supply Staff')){
                if ($request->is('users') || $request->is('users/*')) {
                    return redirect()->route('dashboard')->with('error1', 'You do not have permission to access this page');
                }
            }
            if(auth()->user()->hasRole('Campus Admin')){
                if ($request->is('users*', 'view*', 'technician*', 'properties/list/edit*', 'purchases*', 'settings/system-name')) {
                    // But allow access only if the route is exactly 'view/accntperson' or starts with it
                    if (! $request->is('view/accntperson*')) {
                        return redirect()->route('dashboard')->with('error', 'You do not have permission to access this page');
                    }
                }
            }
            if(auth()->user()->hasRole('Staff')) {
                // restrict access to certain pages
                if ($request->is('users', 'office') || $request->is('users/*', 'office/*')) {
                    return redirect()->route('dashboard')->with('error1', 'You do not have permission to access this page');
                }
            }
        }else{
            return redirect()->route('getLogin')->with('error','You have to Sign In first to access this page');
        }
        return $next($request)->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate')
                              ->header('Pragma','no-cache')
                              ->header('Expires','Sat 01 Jan 1990 00:00:00 GMT');
    }
}
