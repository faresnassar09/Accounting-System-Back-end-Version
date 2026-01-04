<?php

namespace Modules\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCompany
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {



        if (!Auth::guard('admin')?->user()?->organization_id ) {

            return back(); 
 
        }


        return $next($request);
    }
}
 