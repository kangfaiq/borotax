<?php

namespace Tests\Feature;

use App\Domain\Reklame\Models\HargaPatokanReklame;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\ReklameSubJenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederReklameSubJenisPajakTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_includes_reklame_sub_jenis_seed_data(): void
    {
        $databaseSeederSource = file_get_contents((new \ReflectionClass(DatabaseSeeder::class))->getFileName());

        $this->assertStringContainsString('ReklameSubJenisPajakSeeder::class', $databaseSeederSource);

        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            ReklameSubJenisPajakSeeder::class,
        ]);

        $this->assertDatabaseHas('harga_patokan_reklame', [
            'kode' => 'RKL_LED_VIDEOTRON',
            'is_active' => true,
            'is_insidentil' => false,
        ]);

        $this->assertDatabaseHas('harga_patokan_reklame', [
            'kode' => 'RKL_SPANDUK',
            'is_active' => true,
            'is_insidentil' => true,
        ]);

        $this->assertDatabaseHas('sub_jenis_pajak', [
            'kode' => 'REKLAME_TETAP',
            'is_active' => true,
        ]);

        $this->assertSame(20, HargaPatokanReklame::where('kode', 'like', 'RKL_%')->where('is_active', true)->count());
    }
}