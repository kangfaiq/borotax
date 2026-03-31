<?php

namespace Tests\Feature;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Shared\Services\DocumentPreviewService;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Pages\PreviewDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentPreviewAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_backoffice_user_can_open_document_preview_catalog_and_all_pdfs_without_persisting_document_data(): void
    {
        $this->assertDocumentTablesRemainEmpty();

        $admin = $this->createAdminPanelUser('admin');

        $this->actingAs($admin)
            ->get(route('document-previews.index'))
            ->assertOk()
            ->assertSee('Katalog dokumen cetak BOROTAX tanpa data database');

        $this->actingAs($admin)
            ->get(PreviewDokumen::getUrl(panel: 'admin'))
            ->assertOk()
            ->assertSee('Preview semua dokumen tanpa insert database');

        foreach (app(DocumentPreviewService::class)->catalog() as $preview) {
            $response = $this->actingAs($admin)->get(route('document-previews.show', $preview['slug']));

            $response->assertOk();
            $this->assertStringContainsString('application/pdf', $response->headers->get('content-type', ''));
        }

        $downloadResponse = $this->actingAs($admin)->get(route('document-previews.show', 'billing-regular') . '?download=1');

        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment;', $downloadResponse->headers->get('content-disposition', ''));

        $this->assertDocumentTablesRemainEmpty();
    }

    public function test_portal_user_cannot_access_document_previews(): void
    {
        $portalUser = $this->createPortalUserFixture();

        $this->actingAs($portalUser)
            ->get(route('document-previews.index'))
            ->assertNotFound();

        $this->actingAs($portalUser)
            ->get(route('document-previews.show', 'billing-regular'))
            ->assertNotFound();
    }

    public function test_non_admin_backoffice_roles_cannot_access_document_previews(): void
    {
        foreach (['verifikator', 'petugas'] as $role) {
            $user = $this->createAdminPanelUser($role);

            $this->actingAs($user)
                ->get(route('document-previews.index'))
                ->assertNotFound();

            $this->actingAs($user)
                ->get(route('document-previews.show', 'billing-regular'))
                ->assertNotFound();

            $response = $this->actingAs($user)
                ->get(PreviewDokumen::getUrl(panel: 'admin'));

            $this->assertContains($response->getStatusCode(), [302, 403]);
        }
    }

    private function assertDocumentTablesRemainEmpty(): void
    {
        $this->assertSame(0, Tax::count());
        $this->assertSame(0, WajibPajak::count());
        $this->assertSame(0, StpdManual::count());
        $this->assertSame(0, SkpdReklame::count());
        $this->assertSame(0, SkpdAirTanah::count());
        $this->assertSame(0, TaxAssessmentLetter::count());
    }

    private function createAdminPanelUser(string $role): User
    {
        return User::create([
            'name' => Str::headline($role) . ' Preview User',
            'nama_lengkap' => Str::headline($role) . ' Preview User',
            'email' => sprintf('%s-preview-%s@example.test', $role, Str::random(8)),
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);
    }
}