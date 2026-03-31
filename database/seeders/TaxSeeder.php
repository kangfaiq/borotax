<?php

namespace Database\Seeders;

use App\Domain\Tax\Models\Tax;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Pastikan ada user dan master data
        $users = User::all();
        $jenisPajaks = JenisPajak::all();

        if ($users->isEmpty() || $jenisPajaks->isEmpty()) {
            return;
        }

        // Generate 50 transaksi dummy
        for ($i = 0; $i < 50; $i++) {
            $jp = $jenisPajaks->random();
            $sub = SubJenisPajak::where('jenis_pajak_id', $jp->id)->inRandomOrder()->first();
            $status = $faker->randomElement(['paid', 'pending', 'verified', 'expired']);
            $createdAt = $faker->dateTimeBetween('-6 months', 'now');

            Tax::create([
                'jenis_pajak_id' => $jp->id,
                'sub_jenis_pajak_id' => $sub?->id,
                'user_id' => $users->random()->id,
                'amount' => $faker->numberBetween(100000, 5000000), // Enkripsi otomatis via Trait
                'omzet' => $faker->numberBetween(1000000, 50000000),
                'tarif_persentase' => 10,
                'status' => $status,
                'billing_code' => Tax::generateBillingCode($jp->kode),
                'paid_at' => ($status === 'paid' || $status === 'verified') ? $createdAt : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
