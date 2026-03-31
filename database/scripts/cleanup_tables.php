<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// 1. Hapus data taxes
DB::table('taxes')->truncate();
echo "taxes: truncated\n";

// 2. Hapus data tax_objects
DB::table('tax_objects')->truncate();
echo "tax_objects: truncated\n";

// 3. Hapus data wajib_pajak
DB::table('wajib_pajak')->truncate();
echo "wajib_pajak: truncated\n";

// 4. Hapus users kecuali admin, petugas, verifikator
$deleted = DB::table('users')->whereNotIn('role', ['admin', 'petugas', 'verifikator'])->delete();
echo "users deleted (non-admin/petugas/verifikator): {$deleted}\n";

// 5. Drop reklame_objects dan water_objects
Schema::dropIfExists('reklame_objects');
echo "reklame_objects: dropped\n";

Schema::dropIfExists('water_objects');
echo "water_objects: dropped\n";

DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo "Done.\n";
