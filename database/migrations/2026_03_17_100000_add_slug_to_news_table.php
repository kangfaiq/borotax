<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('news', 'slug')) {
            return;
        }

        Schema::table('news', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Generate slugs for existing records
        $news = DB::table('news')->get();
        foreach ($news as $item) {
            $slug = Str::slug($item->title);
            $originalSlug = $slug;
            $counter = 1;
            while (DB::table('news')->where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            DB::table('news')->where('id', $item->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
