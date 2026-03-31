<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabel Destinasi Wisata
        Schema::create('destinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->text('description');
            $table->text('address');
            $table->enum('category', ['wisata', 'kuliner', 'hotel', 'oleh-oleh', 'hiburan']);
            $table->string('image_url', 255);
            $table->decimal('rating', 2, 1)->default(0);
            $table->integer('review_count')->default(0);
            $table->string('price_range', 50)->nullable();
            $table->json('facilities')->comment('Daftar fasilitas');
            $table->text('phone')->nullable()->comment('🔐 Nomor telepon (terenkripsi)');
            $table->string('website', 255)->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('category');
            $table->index('is_featured');
        });

        // Tabel Berita
        Schema::create('news', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->text('excerpt');
            $table->text('content');
            $table->string('image_url', 255);
            $table->timestamp('published_at');
            $table->string('category', 50);
            $table->string('author', 100)->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('source_url', 255)->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('published_at');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
        Schema::dropIfExists('destinations');
    }
};
