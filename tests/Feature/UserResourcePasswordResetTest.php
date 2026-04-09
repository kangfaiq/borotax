<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use Filament\Forms\Components\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourcePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_backoffice_reset_password_action_shows_password_standards_and_rejects_weak_password(): void
    {
        $admin = $this->createAdminPanelUser('admin');
        $managedUser = $this->makeManagedUser();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->mountTableAction('resetPassword', $managedUser)
            ->assertFormFieldExists('new_password', function (Field $field): bool {
                $helperText = (string) ($field->getChildSchema($field::BELOW_CONTENT_SCHEMA_KEY)?->toHtml() ?? '');

                return str_contains($helperText, 'Standar Password')
                    && str_contains($helperText, 'Panjang minimal password adalah tujuh (7) karakter.');
            })
            ->callMountedTableAction([
                'new_password' => 'abcdefg',
                'new_password_confirmation' => 'abcdefg',
            ])
            ->assertHasTableActionErrors(['new_password']);

        $managedUser->refresh();

        $this->assertTrue(Hash::check('PasswordAwal123!', $managedUser->password));
        $this->assertFalse((bool) $managedUser->must_change_password);
    }

    public function test_backoffice_reset_password_action_accepts_password_that_meets_standard(): void
    {
        $admin = $this->createAdminPanelUser('admin');
        $managedUser = $this->makeManagedUser();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('resetPassword', $managedUser, [
                'new_password' => 'PasswordBaru456!',
                'new_password_confirmation' => 'PasswordBaru456!',
            ])
            ->assertHasNoTableActionErrors();

        $managedUser->refresh();

        $this->assertTrue(Hash::check('PasswordBaru456!', $managedUser->password));
        $this->assertTrue((bool) $managedUser->must_change_password);
        $this->assertNull($managedUser->password_changed_at);
    }

    private function createAdminPanelUser(string $role): User
    {
        return User::create([
            'name' => Str::headline($role) . ' User',
            'nama_lengkap' => Str::headline($role) . ' User',
            'email' => sprintf('%s-%s@example.test', $role, Str::random(8)),
            'password' => Hash::make('PasswordAdmin123!'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);
    }

    private function makeManagedUser(): User
    {
        return User::create([
            'name' => 'Managed User',
            'nama_lengkap' => 'Managed User',
            'email' => 'managed-' . Str::random(6) . '@example.test',
            'password' => Hash::make('PasswordAwal123!'),
            'role' => 'petugas',
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
            'must_change_password' => false,
            'password_changed_at' => now()->subDay(),
        ]);
    }
}