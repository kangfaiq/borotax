<?php

use App\Domain\Auth\Models\User;
use App\Domain\CMS\Models\Destination;
use App\Filament\Resources\DestinationResource\Pages\CreateDestination;
use App\Filament\Resources\DestinationResource\Pages\EditDestination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates destination from comma decimal input', function () {
    $admin = createDestinationDecimalAdminFixture();

    $this->actingAs($admin);

    Livewire::test(CreateDestination::class)
        ->fillForm([
            'name' => 'Destinasi Decimal Baru',
            'slug' => 'destinasi-decimal-baru',
            'category' => 'wisata',
            'description' => 'Destinasi uji decimal.',
            'address' => 'Jl. Wisata Decimal No. 1',
            'image_url' => UploadedFile::fake()->image('destination.jpg', 1600, 900)->size(500),
            'rating' => '4,7',
            'review_count' => 12,
            'price_range' => 'Rp 10.000 - Rp 50.000',
            'is_featured' => true,
            'phone' => '081234567890',
            'website' => 'https://example.test',
            'latitude' => '-7,15234567',
            'longitude' => '111,88123456',
            'facilities' => ['parkir', 'toilet'],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $record = Destination::query()->where('slug', 'destinasi-decimal-baru')->firstOrFail();

    expect((float) $record->rating)->toBe(4.7)
        ->and((float) $record->latitude)->toBe(-7.15234567)
        ->and((float) $record->longitude)->toBe(111.88123456);
});

it('updates destination from comma decimal input', function () {
    $admin = createDestinationDecimalAdminFixture();

    $record = Destination::create([
        'name' => 'Destinasi Lama',
        'slug' => 'destinasi-lama',
        'description' => 'Deskripsi lama.',
        'address' => 'Jl. Lama No. 1',
        'category' => 'wisata',
        'image_url' => 'destinations/sample.jpg',
        'rating' => 4.5,
        'review_count' => 10,
        'price_range' => 'gratis',
        'facilities' => ['parkir'],
        'phone' => '081234567890',
        'website' => 'https://example.test',
        'latitude' => -7.15000000,
        'longitude' => 111.88000000,
        'is_featured' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditDestination::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'name' => 'Destinasi Decimal Update',
            'slug' => 'destinasi-decimal-update',
            'category' => 'wisata',
            'description' => 'Deskripsi baru.',
            'address' => 'Jl. Baru No. 2',
            'image_url' => ['destinations/sample.jpg'],
            'rating' => '4,9',
            'review_count' => 15,
            'price_range' => 'Rp 20.000 - Rp 60.000',
            'is_featured' => true,
            'phone' => '081234567891',
            'website' => 'https://example.org',
            'latitude' => '-7,15432109',
            'longitude' => '111,88987654',
            'facilities' => ['parkir', 'wifi'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $record->refresh();

    expect($record->name)->toBe('Destinasi Decimal Update')
        ->and((float) $record->rating)->toBe(4.9)
        ->and((float) $record->latitude)->toBe(-7.15432109)
        ->and((float) $record->longitude)->toBe(111.88987654);
});

function createDestinationDecimalAdminFixture(): User
{
    return User::create([
        'name' => 'Admin Destination Decimal',
        'nama_lengkap' => 'Admin Destination Decimal',
        'email' => sprintf('admin-destination-%s@example.test', Str::random(8)),
        'password' => Hash::make('password'),
        'role' => 'admin',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}