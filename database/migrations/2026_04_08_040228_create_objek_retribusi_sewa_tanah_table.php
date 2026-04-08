<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('objek_retribusi_sewa_tanah', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('jenis_pajak_id');
            $table->uuid('sub_jenis_pajak_id');
            $table->uuid('tax_object_id')->comment('FK ke objek reklame di tax_objects');

            $table->string('npwpd', 30)->nullable()->index();
            $table->integer('nopd')->default(1);

            // WP data (encrypted)
            $table->text('nik')->comment('encrypted');
            $table->string('nik_hash', 64)->nullable()->index();
            $table->text('nama_pemilik')->comment('encrypted');
            $table->text('alamat_pemilik')->comment('encrypted');

            // Objek data
            $table->text('nama_objek')->comment('encrypted');
            $table->text('alamat_objek')->comment('encrypted');
            $table->decimal('luas_m2', 10, 2)->comment('dikopi dari objek reklame');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('jenis_pajak_id')->references('id')->on('jenis_pajak');
            $table->foreign('sub_jenis_pajak_id')->references('id')->on('sub_jenis_pajak');
            $table->foreign('tax_object_id')->references('id')->on('tax_objects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objek_retribusi_sewa_tanah');
    }
};
