<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Domain\Master\Models\JenisPajak;
use App\Filament\Resources\GebyarSubmissionResource;
use App\Filament\Resources\GebyarSubmissionResource\Pages\ListGebyarSubmissions;
use Database\Seeders\JenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GebyarSubmissionRoleActionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_gebyar_submission_actions_follow_role_rules(string $role, bool $canVerify): void
    {
        $submission = $this->createPendingGebyarSubmission();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(GebyarSubmissionResource::getUrl('index'));

        if (! $canVerify) {
            $this->assertContains($indexResponse->getStatusCode(), [302, 403, 404]);

            return;
        }

        $this->assertContains($indexResponse->getStatusCode(), [200, 302]);

        Livewire::test(ListGebyarSubmissions::class)
            ->assertCanSeeTableRecords([$submission])
            ->assertTableActionVisible('verify', $submission)
            ->assertTableActionVisible('reject', $submission);
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'verifikator' => ['verifikator', true],
            'petugas' => ['petugas', false],
        ];
    }

    private function createPendingGebyarSubmission(): GebyarSubmission
    {
        $this->seed(JenisPajakSeeder::class);

        $user = User::create([
            'name' => 'Gebyar User',
            'email' => sprintf('gebyar-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567800',
            'nama_lengkap' => 'Gebyar User',
            'alamat' => 'Jl. Pemuda No. 8',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
            'total_kupon_undian' => 0,
        ]);

        return GebyarSubmission::create([
            'user_id' => $user->id,
            'user_nik' => '3522011234567800',
            'user_name' => 'Gebyar User',
            'jenis_pajak_id' => JenisPajak::firstOrFail()->id,
            'place_name' => 'Warung Uji',
            'transaction_date' => now()->subDay()->toDateString(),
            'transaction_amount' => '150000',
            'transaction_amount_hash' => User::generateHash('150000'),
            'image_url' => 'gebyar/sample.jpg',
            'original_image_url' => 'gebyar/sample-original.jpg',
            'status' => 'pending',
            'period_year' => (int) now()->format('Y'),
            'kupon_count' => 1,
        ]);
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
}