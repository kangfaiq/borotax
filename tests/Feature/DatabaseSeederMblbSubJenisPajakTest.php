<?php

namespace Tests\Feature;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanMblb;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\HargaPatokanMblbSeeder;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederMblbSubJenisPajakTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_includes_mblb_sub_jenis_seed_data(): void
    {
        $databaseSeederSource = file_get_contents((new \ReflectionClass(DatabaseSeeder::class))->getFileName());

        $this->assertStringContainsString('SubJenisPajakSeeder::class', $databaseSeederSource);
        $this->assertStringNotContainsString('MblbSubJenisPajakSeeder::class', $databaseSeederSource);

        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->assertDatabaseHas('sub_jenis_pajak', [
            'kode' => 'MBLB_WP',
            'is_active' => true,
            'is_insidentil' => false,
        ]);

        $this->assertDatabaseHas('sub_jenis_pajak', [
            'kode' => 'MBLB_WAPU',
            'is_active' => true,
            'is_insidentil' => false,
        ]);

        $this->assertSame(2, SubJenisPajak::where('kode', 'like', 'MBLB_%')->where('is_active', true)->count());
    }

    public function test_harga_patokan_mblb_seed_data_is_shared_for_all_mblb_sub_jenis(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            HargaPatokanMblbSeeder::class,
        ]);

        $this->assertDatabaseHas('harga_patokan_mblb', [
            'nama_mineral' => 'Pasir Pasang',
            'sub_jenis_pajak_id' => null,
            'is_active' => true,
        ]);

        $this->assertSame(15, HargaPatokanMblb::count());
        $this->assertSame(0, HargaPatokanMblb::whereNotNull('sub_jenis_pajak_id')->count());
    }
}