<?php

namespace App\Http\Middleware;

use App\Domain\Auth\Support\PasswordChangeRequirement;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if (in_array($user->role, ['admin', 'verifikator', 'petugas'])) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            $contract = PasswordChangeRequirement::forApi(true);

            return new JsonResponse([
                'success' => false,
                'message' => 'Password harus diganti sebelum melanjutkan.',
                'data' => [
                    'must_change_password' => true,
                    'allowed_endpoints' => collect($contract['allowed_actions'])
                        ->map(fn (array $action) => $action['method'] . ' ' . $action['endpoint'])
                        ->all(),
                    'auth_requirements' => $contract,
                ],
            ], 423);
        }

        return new RedirectResponse(
            route('portal.force-password.form')
        );
    }
}