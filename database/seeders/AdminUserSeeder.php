<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@bapenda.bojonegorokab.go.id'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin123'),
                'nama_lengkap' => 'Administrator Bapenda',
                'status' => 'verified',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Verifikator User
        User::firstOrCreate(
            ['email' => 'verifikator@bapenda.bojonegorokab.go.id'],
            [
                'name' => 'verifikator',
                'password' => Hash::make('verifikator123'),
                'nama_lengkap' => 'Petugas Verifikator',
                'status' => 'verified',
                'role' => 'verifikator',
                'email_verified_at' => now(),
            ]
        );

        // Petugas User
        User::firstOrCreate(
            ['email' => 'petugas@bapenda.bojonegorokab.go.id'],
            [
                'name' => 'petugas',
                'password' => Hash::make('petugas123'),
                'nama_lengkap' => 'Petugas Lapangan',
                'status' => 'verified',
                'role' => 'petugas',
                'email_verified_at' => now(),
            ]
        );
    }
}
