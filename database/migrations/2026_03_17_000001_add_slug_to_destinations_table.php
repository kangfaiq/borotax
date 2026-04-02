<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('destinations', 'slug')) {
            return;
        }

        Schema::table('destinations', function (Blueprint $table) {
            $table->string('slug', 150)->unique()->after('name');
        });

        // Generate slugs for existing records
        $destinations = DB::table('destinations')->get();
        foreach ($destinations as $destination) {
            $slug = Str::slug($destination->name);
            $originalSlug = $slug;
            $counter = 1;
            while (DB::table('destinations')->where('slug', $slug)->where('id', '!=', $destination->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            DB::table('destinations')->where('id', $destination->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
