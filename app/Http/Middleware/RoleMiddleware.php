<?php





namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        // Check if the user is authenticated and has the correct role
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);  // Allow the request to pass
        }

        // If not an admin, return a forbidden response
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
