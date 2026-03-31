<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\DaftarWajibPajakResource;
use App\Filament\Resources\DaftarWajibPajakResource\Pages\ListDaftarWajibPajaks;
use App\Filament\Resources\TaxObjectResource;
use App\Filament\Resources\TaxObjectResource\Pages\ListTaxObjects;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SharedCrudResourceActionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('taxObjectRoleProvider')]
    public function test_tax_object_resource_actions_follow_role_rules(string $role, bool $canAccess, bool $canCreateAndEdit): void
    {
        $taxObject = $this->makeTaxObject();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(TaxObjectResource::getUrl('index'));
        $createResponse = $this->get(TaxObjectResource::getUrl('create'));
        $viewResponse = $this->get(TaxObjectResource::getUrl('view', ['record' => $taxObject]));
        $editResponse = $this->get(TaxObjectResource::getUrl('edit', ['record' => $taxObject]));

        $this->assertAccessExpectation($indexResponse->getStatusCode(), $canAccess, "tax object index for {$role}");
        $this->assertAccessExpectation($createResponse->getStatusCode(), $canCreateAndEdit, "tax object create for {$role}");
        $this->assertAccessExpectation($viewResponse->getStatusCode(), $canAccess, "tax object view for {$role}");
        $this->assertAccessExpectation($editResponse->getStatusCode(), $canCreateAndEdit, "tax object edit for {$role}");

        if (! $canAccess) {
            return;
        }

        $component = Livewire::test(ListTaxObjects::class)
            ->assertCanSeeTableRecords([$taxObject])
            ->assertTableActionVisible('view', $taxObject);

        if ($canCreateAndEdit) {
            $component->assertTableActionVisible('edit', $taxObject);
        } else {
            $component->assertTableActionHidden('edit', $taxObject);
        }
    }

    #[DataProvider('daftarRoleProvider')]
    public function test_daftar_wajib_pajak_resource_actions_follow_role_rules(string $role, bool $canCreateAndEdit): void
    {
        $record = $this->makeDaftarWajibPajak();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(DaftarWajibPajakResource::getUrl('index'));
        $this->assertAccessExpectation($indexResponse->getStatusCode(), $canCreateAndEdit, "daftar wajib pajak index for {$role}");

        $createResponse = $this->get(DaftarWajibPajakResource::getUrl('create'));
        $this->assertAccessExpectation($createResponse->getStatusCode(), $canCreateAndEdit, "daftar wajib pajak create for {$role}");

        $editResponse = $this->get(DaftarWajibPajakResource::getUrl('edit', ['record' => $record]));
        $this->assertAccessExpectation($editResponse->getStatusCode(), $canCreateAndEdit, "daftar wajib pajak edit for {$role}");

        if (! $canCreateAndEdit) {
            return;
        }

        Livewire::test(ListDaftarWajibPajaks::class)
            ->assertCanSeeTableRecords([$record])
            ->assertTableActionVisible('view', $record)
            ->assertTableActionVisible('edit', $record);
    }

    public static function taxObjectRoleProvider(): array
    {
        return [
            'admin' => ['admin', true, true],
            'petugas' => ['petugas', true, true],
            'verifikator' => ['verifikator', false, false],
        ];
    }

    public static function daftarRoleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'petugas' => ['petugas', true],
            'verifikator' => ['verifikator', false],
        ];
    }

    private function makeTaxObject(): TaxObject
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        return $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture());
    }

    private function makeDaftarWajibPajak(): WajibPajak
    {
        return $this->createApprovedWajibPajakFixture();
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

    private function assertAccessExpectation(int $statusCode, bool $isAllowed, string $context): void
    {
        if ($isAllowed) {
            $this->assertSame(200, $statusCode, "Expected 200 for {$context}, got {$statusCode}.");

            return;
        }

        $this->assertContains($statusCode, [403, 404], "Expected 403/404 for {$context}, got {$statusCode}.");
    }
}