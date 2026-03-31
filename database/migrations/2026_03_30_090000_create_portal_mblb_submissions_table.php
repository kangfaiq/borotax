<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('portal_mblb_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jenis_pajak_id')->constrained('jenis_pajak');
            $table->foreignUuid('sub_jenis_pajak_id')->nullable()->constrained('sub_jenis_pajak')->nullOnDelete();
            $table->foreignUuid('tax_object_id')->constrained('tax_objects')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('masa_pajak_bulan');
            $table->unsignedSmallInteger('masa_pajak_tahun');
            $table->decimal('tarif_persen', 8, 2);
            $table->decimal('opsen_persen', 8, 2)->default(25);
            $table->decimal('total_dpp', 18, 2);
            $table->decimal('pokok_pajak', 18, 2);
            $table->decimal('opsen', 18, 2)->default(0);
            $table->json('detail_items');
            $table->string('attachment_path');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('approved_tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->timestamps();

            $table->index(['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun'], 'portal_mblb_submission_period_idx');
            $table->index(['status', 'created_at'], 'portal_mblb_submission_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_mblb_submissions');
    }
};