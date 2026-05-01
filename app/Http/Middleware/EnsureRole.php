<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        $roles = array_filter(array_map('trim', $roles));
        if ($roles === []) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(403, 'You do not have permission to access this page.');
        }

        return redirect()
            ->to(route('dashboard', [], false))
            ->with('warning', 'You do not have permission to access this page.');
    }
}