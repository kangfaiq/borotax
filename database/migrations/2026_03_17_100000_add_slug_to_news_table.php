<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Generate slugs for existing records
        $news = \App\Domain\CMS\Models\News::all();
        foreach ($news as $item) {
            $slug = Str::slug($item->title);
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Domain\CMS\Models\News::where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $item->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
