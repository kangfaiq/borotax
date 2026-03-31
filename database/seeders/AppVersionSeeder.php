<?php

namespace Database\Seeders;

use App\Domain\Shared\Models\AppVersion;
use Illuminate\Database\Seeder;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AppVersion::firstOrCreate(
            ['platform' => 'android'],
            [
                'min_version' => '1.0.0',
                'latest_version' => '1.0.0',
                'force_update' => false,
                'maintenance_mode' => false,
                'message' => null,
                'store_url' => 'https://play.google.com/store/apps/details?id=id.go.bojonegorokab.borotax',
            ]
        );

        AppVersion::firstOrCreate(
            ['platform' => 'ios'],
            [
                'min_version' => '1.0.0',
                'latest_version' => '1.0.0',
                'force_update' => false,
                'maintenance_mode' => false,
                'message' => null,
                'store_url' => 'https://apps.apple.com/id/app/borotax/id000000000',
            ]
        );
    }
}
