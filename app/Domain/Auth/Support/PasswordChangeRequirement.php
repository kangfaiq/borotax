<?php

namespace App\Domain\Auth\Support;

class PasswordChangeRequirement
{
    public static function forApi(bool $mustChangePassword): array
    {
        return [
            'must_change_password' => $mustChangePassword,
            'blocking' => $mustChangePassword,
            'error_code' => $mustChangePassword ? 'PASSWORD_CHANGE_REQUIRED' : null,
            'required_action' => $mustChangePassword ? [
                'type' => 'change_password',
                'screen' => 'force_change_password',
                'method' => 'POST',
                'endpoint' => '/api/v1/update-password',
                'message' => 'Password harus diganti sebelum melanjutkan.',
            ] : null,
            'allowed_actions' => $mustChangePassword ? [
                [
                    'key' => 'view_profile',
                    'method' => 'GET',
                    'endpoint' => '/api/v1/profile',
                ],
                [
                    'key' => 'change_password',
                    'method' => 'POST',
                    'endpoint' => '/api/v1/update-password',
                ],
                [
                    'key' => 'logout',
                    'method' => 'POST',
                    'endpoint' => '/api/v1/logout',
                ],
            ] : [],
        ];
    }
}