<?php

namespace App\Domain\Auth\Support;

use App\Domain\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class SingleSessionManager
{
    public const SESSION_KEY = 'single_session_id_portal';

    public const PORTAL_SESSION_KEY = 'single_session_id_portal';

    public const BACKOFFICE_SESSION_KEY = 'single_session_id_backoffice';

    private const TOKEN_ABILITY_PREFIX = 'single-session:';

    public static function startWebSession(User $user, string $plainPassword, Request $request, string $channel, string $guard = 'web'): array
    {
        $replacedSessionNotice = self::buildReplacementNotice($user);
        $sessionId = self::issueNewSession($user, $request, $channel);

        auth($guard)->logoutOtherDevices($plainPassword);
        self::attachToCurrentWebSession($user, $sessionId, $channel);

        return [
            'session_id' => $sessionId,
            'replaced_session_notice' => $replacedSessionNotice,
            'active_session' => self::sessionContext($user),
        ];
    }

    public static function startApiSession(User $user, Request $request, string $channel = 'mobile_api', string $tokenName = 'BorotaxApp'): array
    {
        $replacedSessionNotice = self::buildReplacementNotice($user);
        $sessionId = self::issueNewSession($user, $request, $channel);
        $plainTextToken = $user->createToken($tokenName, [self::tokenAbility($sessionId)])->plainTextToken;

        return [
            'session_id' => $sessionId,
            'token' => $plainTextToken,
            'replaced_session_notice' => $replacedSessionNotice,
            'active_session' => self::sessionContext($user),
        ];
    }

    public static function issueNewSession(User $user, Request $request, string $channel): string
    {
        $sessionId = (string) Str::uuid();

        $user->forceFill([
            'active_session_id' => $sessionId,
            'active_session_channel' => $channel,
            'active_session_ip' => self::resolveIpAddress($request),
            'active_session_user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'active_session_started_at' => now(),
        ])->save();

        return $sessionId;
    }

    public static function attachToCurrentWebSession(User $user, ?string $sessionId = null, ?string $channel = null): void
    {
        if (! request()->hasSession()) {
            return;
        }

        $sessionKey = self::sessionKeyForChannel($channel ?? $user->active_session_channel);

        if (! $sessionKey) {
            return;
        }

        request()->session()->put($sessionKey, $sessionId ?? $user->active_session_id);
    }

    public static function tokenMatchesActiveSession(User $user, ?PersonalAccessToken $token = null): bool
    {
        $activeSessionId = $user->active_session_id;
        $token ??= $user->currentAccessToken();

        if (blank($activeSessionId) || ! $token) {
            return true;
        }

        return in_array(self::tokenAbility($activeSessionId), $token->abilities, true);
    }

    public static function clearCurrentSession(User $user, Request $request): void
    {
        $shouldClearActiveSession = false;
        $sessionKey = self::sessionKeyForUser($user);

        if ($request->hasSession()) {
            if ($sessionKey) {
                $shouldClearActiveSession = $request->session()->get($sessionKey) === $user->active_session_id;
                $request->session()->forget($sessionKey);
            }
        }

        $currentAccessToken = $user->currentAccessToken();

        if ($currentAccessToken instanceof PersonalAccessToken) {
            $shouldClearActiveSession = $shouldClearActiveSession || self::tokenMatchesActiveSession($user);
            $currentAccessToken->delete();
        }

        if ($shouldClearActiveSession) {
            $user->tokens()->delete();

            $user->forceFill([
                'active_session_id' => null,
                'active_session_channel' => null,
                'active_session_ip' => null,
                'active_session_user_agent' => null,
                'active_session_started_at' => null,
            ])->save();
        }
    }

    public static function buildReplacementNotice(User $user): ?string
    {
        $summary = self::sessionSummary($user);

        if (! $summary) {
            return null;
        }

        return 'Akun ini sebelumnya masih aktif di ' . $summary . '. Sesi sebelumnya telah diakhiri.';
    }

    public static function buildRevokedSessionMessage(User $user): string
    {
        $summary = self::sessionSummary($user);

        if (! $summary) {
            return 'Sesi Anda berakhir karena akun digunakan pada perangkat lain.';
        }

        return 'Sesi Anda berakhir karena akun ini login kembali di ' . $summary . '.';
    }

    public static function sessionContext(User $user): ?array
    {
        if (blank($user->active_session_id)) {
            return null;
        }

        return [
            'channel' => $user->active_session_channel,
            'channel_label' => self::channelLabel($user->active_session_channel),
            'ip' => $user->active_session_ip,
            'device' => self::deviceLabel($user->active_session_user_agent),
            'user_agent' => $user->active_session_user_agent,
            'started_at' => $user->active_session_started_at?->toIso8601String(),
            'started_at_label' => self::formatStartedAt($user->active_session_started_at),
        ];
    }

    public static function sessionSummary(User $user): ?string
    {
        $context = self::sessionContext($user);

        if (! $context) {
            return null;
        }

        $parts = [strtolower($context['channel_label'])];

        if ($context['started_at_label']) {
            $parts[] = 'pada ' . $context['started_at_label'];
        }

        if ($context['device']) {
            $parts[] = 'melalui ' . $context['device'];
        }

        if ($context['ip']) {
            $parts[] = 'IP ' . $context['ip'];
        }

        return implode(', ', $parts);
    }

    public static function tokenAbility(string $sessionId): string
    {
        return self::TOKEN_ABILITY_PREFIX . $sessionId;
    }

    public static function sessionKeyForUser(User $user): string
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas'])
            ? self::BACKOFFICE_SESSION_KEY
            : self::PORTAL_SESSION_KEY;
    }

    public static function sessionKeyForChannel(?string $channel): ?string
    {
        return match ($channel) {
            'portal_web' => self::PORTAL_SESSION_KEY,
            'admin_panel' => self::BACKOFFICE_SESSION_KEY,
            default => null,
        };
    }

    private static function resolveIpAddress(Request $request): ?string
    {
        return $request->ip();
    }

    private static function channelLabel(?string $channel): string
    {
        return match ($channel) {
            'portal_web' => 'Portal Web',
            'admin_panel' => 'Backoffice Admin',
            'mobile_api' => 'Mobile API',
            default => 'perangkat lain',
        };
    }

    private static function formatStartedAt(mixed $startedAt): ?string
    {
        if (! $startedAt instanceof Carbon) {
            return null;
        }

        return $startedAt->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i');
    }

    private static function deviceLabel(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return null;
        }

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/'), str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/') => 'Safari',
            default => 'Browser tidak dikenal',
        };

        $platform = match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone'), str_contains($userAgent, 'iPad'), str_contains($userAgent, 'iOS') => 'iOS',
            str_contains($userAgent, 'Mac OS X'), str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => null,
        };

        return $platform ? $browser . ' / ' . $platform : $browser;
    }
}