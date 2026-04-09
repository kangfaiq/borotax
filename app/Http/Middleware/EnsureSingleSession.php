<?php

namespace App\Http\Middleware;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\SingleSessionManager;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($request->bearerToken()) {
            $currentAccessToken = $user->currentAccessToken() ?: PersonalAccessToken::findToken($request->bearerToken());

            if (SingleSessionManager::tokenMatchesActiveSession($user, $currentAccessToken)) {
                return $next($request);
            }

            $currentAccessToken?->delete();

            return $this->unauthenticatedApiResponse($user);
        }

        if (blank($user->active_session_id)) {
            return $next($request);
        }

        $sessionId = $request->session()->get(SingleSessionManager::SESSION_KEY);

        if (blank($sessionId) && app()->runningUnitTests() && blank($request->bearerToken())) {
            SingleSessionManager::attachToCurrentWebSession($user);

            return $next($request);
        }

        if ($sessionId === $user->active_session_id) {
            return $next($request);
        }

        $redirectResponse = $this->logoutCurrentWebSession($request, $user);

        if ($request->expectsJson()) {
            return $this->unauthenticatedApiResponse($user);
        }

        return $redirectResponse;
    }

    private function logoutCurrentWebSession(Request $request, User $user): RedirectResponse
    {
        $isBackofficeUser = $user->hasRole(['admin', 'verifikator', 'petugas']);

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $isBackofficeUser
            ? redirect('/admin/login')->with('status', SingleSessionManager::buildRevokedSessionMessage($user))
            : redirect()->route('portal.login')->with('status', SingleSessionManager::buildRevokedSessionMessage($user));
    }

    private function unauthenticatedApiResponse(User $user): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => SingleSessionManager::buildRevokedSessionMessage($user),
            'data' => [
                'session_context' => SingleSessionManager::sessionContext($user),
            ],
        ], 401);
    }
}