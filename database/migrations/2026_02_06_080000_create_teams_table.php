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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert 10 default teams
        DB::table('teams')->insert([
            ['name' => 'Tim 1', 'description' => 'Tim Lapangan 1', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 2', 'description' => 'Tim Lapangan 2', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 3', 'description' => 'Tim Lapangan 3', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 4', 'description' => 'Tim Lapangan 4', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 5', 'description' => 'Tim Lapangan 5', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 6', 'description' => 'Tim Lapangan 6', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 7', 'description' => 'Tim Lapangan 7', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 8', 'description' => 'Tim Lapangan 8', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 9', 'description' => 'Tim Lapangan 9', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tim 10', 'description' => 'Tim Lapangan 10', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
