<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('jenis_pajak')->where('nama', 'Pajak Hotel')->update(['nama' => 'PBJT atas Jasa Perhotelan']);
        DB::table('jenis_pajak')->where('nama', 'Pajak Restoran')->update(['nama' => 'PBJT atas Makanan dan/atau Minuman']);
        DB::table('jenis_pajak')->where('nama', 'Pajak Hiburan')->update(['nama' => 'PBJT atas Jasa Kesenian dan Hiburan']);
        DB::table('jenis_pajak')->where('nama', 'Pajak Parkir')->update(['nama' => 'PBJT atas Jasa Parkir']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('jenis_pajak')->where('nama', 'PBJT atas Jasa Perhotelan')->update(['nama' => 'Pajak Hotel']);
        DB::table('jenis_pajak')->where('nama', 'PBJT atas Makanan dan/atau Minuman')->update(['nama' => 'Pajak Restoran']);
        DB::table('jenis_pajak')->where('nama', 'PBJT atas Jasa Kesenian dan Hiburan')->update(['nama' => 'Pajak Hiburan']);
        DB::table('jenis_pajak')->where('nama', 'PBJT atas Jasa Parkir')->update(['nama' => 'Pajak Parkir']);
    }
};
