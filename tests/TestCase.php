<?php

namespace Tests;

use App\Domain\Master\Models\Pimpinan;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\KelompokLokasiJalanSeeder;
use Database\Seeders\ReklameSubJenisPajakSeeder;
use Database\Seeders\ReklameTariffSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use RuntimeException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\BuildsDomainFixtures;

abstract class TestCase extends BaseTestCase
{
    use BuildsDomainFixtures;
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if (! app()->environment('testing')) {
            throw new RuntimeException('Test suite must run with APP_ENV=testing.');
        }

        $defaultConnection = (string) config('database.default');
        $databaseName = (string) config("database.connections.{$defaultConnection}.database");

        if ($databaseName !== 'borotax_test') {
            throw new RuntimeException("Unsafe test database [{$databaseName}]. Expected [borotax_test].");
        }
    }

    protected function seedReklameTaxReferences(array $additionalSeeders = []): void
    {
        $this->seed(array_merge([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            ReklameSubJenisPajakSeeder::class,
            KelompokLokasiJalanSeeder::class,
            ReklameTariffSeeder::class,
        ], $additionalSeeders));
    }

    protected function seedPimpinanReferences(): void
    {
        Pimpinan::firstOrCreate(
            ['nip' => '196512101990031005'],
            [
                'kab' => 'Bojonegoro',
                'opd' => 'Badan Pendapatan Daerah',
                'jabatan' => 'Kepala Badan Pendapatan Daerah',
                'bidang' => null,
                'sub_bidang' => null,
                'nama' => 'Kepala Bapenda',
                'pangkat' => 'Pembina Utama Muda (IV/c)',
            ]
        );

        Pimpinan::firstOrCreate(
            ['nip' => '197005151995031002'],
            [
                'kab' => 'Bojonegoro',
                'opd' => 'Badan Pendapatan Daerah',
                'jabatan' => 'Kepala Bidang',
                'bidang' => 'Pendataan dan Penetapan',
                'sub_bidang' => null,
                'nama' => 'Kabid Pendataan',
                'pangkat' => 'Pembina (IV/a)',
            ]
        );
    }
}
