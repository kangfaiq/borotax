<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):Response $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (auth()->guard($guard)->check()) {
                $user = auth()->guard($guard)->user();

                // Admin/verifikator/petugas → arahkan ke admin panel
                if (in_array($user->role, ['admin', 'verifikator', 'petugas'])) {
                    return redirect('/admin');
                }

                // Wajib pajak / user lain → arahkan ke portal dashboard
                return redirect('/portal/dashboard');
            }
        }

        return $next($request);
    }
}
