<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles Allowed user role. 
    */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();
        foreach ($roles as $role){
            if($user->role && strtolower($user->role->name) === strtolower($role)){
                return $next($request);
            }
        }
        return response()->json(['message' => 'Forbidden: Insufficient role permissions.'], 403);
    }
}
