<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('slug', 150)->unique()->after('name');
        });

        // Generate slugs for existing records
        $destinations = \App\Domain\CMS\Models\Destination::all();
        foreach ($destinations as $destination) {
            $slug = Str::slug($destination->name);
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Domain\CMS\Models\Destination::where('slug', $slug)->where('id', '!=', $destination->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $destination->slug = $slug;
            $destination->saveQuietly();
        }
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
