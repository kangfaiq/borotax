<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use Filament\Pages\BasePage;
use Filament\Resources\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelRoleSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_role_can_access_expected_filament_pages(string $role): void
    {
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        foreach ($this->discoverFilamentPages() as $pageClass) {
            if ($pageClass === \App\Filament\Pages\Auth\Login::class) {
                continue;
            }

            $response = $this->get($pageClass::getUrl());

            $this->assertAccessExpectation(
                $response->getStatusCode(),
                $pageClass::canAccess(),
                "page {$pageClass} for role {$role}"
            );
        }
    }

    #[DataProvider('roleProvider')]
    public function test_role_can_access_expected_filament_resource_pages(string $role): void
    {
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        foreach ($this->discoverFilamentResources() as $resourceClass) {
            $pages = $resourceClass::getPages();

            if (array_key_exists('index', $pages)) {
                $response = $this->get($resourceClass::getUrl('index'));

                $this->assertAccessExpectation(
                    $response->getStatusCode(),
                    $resourceClass::canAccess() && $resourceClass::canViewAny(),
                    "resource index {$resourceClass} for role {$role}"
                );
            }

            if (array_key_exists('create', $pages)) {
                $response = $this->get($resourceClass::getUrl('create'));

                $this->assertAccessExpectation(
                    $response->getStatusCode(),
                    $resourceClass::canAccess() && $resourceClass::canCreate(),
                    "resource create {$resourceClass} for role {$role}"
                );
            }
        }
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'petugas' => ['petugas'],
            'verifikator' => ['verifikator'],
        ];
    }

    private function assertAccessExpectation(int $statusCode, bool $isAllowed, string $context): void
    {
        if ($isAllowed) {
            $this->assertSame(200, $statusCode, "Expected 200 for {$context}, got {$statusCode}.");

            return;
        }

        $this->assertContains(
            $statusCode,
            [403, 404],
            "Expected 403/404 for {$context}, got {$statusCode}."
        );
    }

    private function createAdminPanelUser(string $role): User
    {
        return User::create([
            'name' => Str::headline($role) . ' User',
            'nama_lengkap' => Str::headline($role) . ' User',
            'email' => sprintf('%s-%s@example.test', $role, Str::random(8)),
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);
    }

    /**
     * @return array<int, class-string<BasePage>>
     */
    private function discoverFilamentPages(): array
    {
        return collect(File::allFiles(app_path('Filament/Pages')))
            ->map(fn ($file) => $this->buildClassName($file->getRelativePathname(), 'App\\Filament\\Pages'))
            ->filter(fn (string $class) => class_exists($class) && is_subclass_of($class, BasePage::class))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, class-string<Resource>>
     */
    private function discoverFilamentResources(): array
    {
        return collect(File::allFiles(app_path('Filament/Resources')))
            ->filter(function ($file): bool {
                return Str::endsWith($file->getFilename(), 'Resource.php')
                    && ! str_contains($file->getRelativePathname(), 'Pages\\')
                    && ! str_contains($file->getRelativePathname(), 'Widgets\\')
                    && ! str_contains($file->getRelativePathname(), 'Concerns\\');
            })
            ->map(fn ($file) => $this->buildClassName($file->getRelativePathname(), 'App\\Filament\\Resources'))
            ->filter(fn (string $class) => class_exists($class) && is_subclass_of($class, Resource::class))
            ->sort()
            ->values()
            ->all();
    }

    private function buildClassName(string $relativePathname, string $baseNamespace): string
    {
        $normalizedPath = str_replace(['/', '.php'], ['\\', ''], $relativePathname);

        return $baseNamespace . '\\' . $normalizedPath;
    }
}