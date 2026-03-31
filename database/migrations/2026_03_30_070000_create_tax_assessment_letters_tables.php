<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_assessment_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('source_tax_id')->index();
            $table->uuid('generated_tax_id')->nullable()->index();
            $table->uuid('parent_letter_id')->nullable()->index();
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('tax_object_id')->nullable()->index();

            $table->enum('letter_type', ['skpdkb', 'skpdkbt', 'skpdlb', 'skpdn'])->index();
            $table->enum('issuance_reason', [
                'pemeriksaan',
                'jabatan_tidak_sampaikan_sptpd',
                'jabatan_tidak_kooperatif',
                'data_baru',
                'lebih_bayar',
                'nihil',
            ])->index();
            $table->enum('status', ['draft', 'disetujui', 'ditolak'])->default('draft')->index();

            $table->string('document_number', 50)->nullable()->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();

            $table->text('base_amount')->nullable()->comment('Base principal under/over payment amount');
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->unsignedInteger('interest_months')->default(0);
            $table->text('interest_amount')->nullable();
            $table->decimal('surcharge_rate', 5, 2)->default(0);
            $table->text('surcharge_amount')->nullable();
            $table->text('total_assessment')->nullable();
            $table->text('available_credit')->nullable();

            $table->text('notes')->nullable();
            $table->text('verification_notes')->nullable();

            $table->uuid('created_by')->nullable();
            $table->string('created_by_name', 100)->nullable();
            $table->uuid('verified_by')->nullable();
            $table->string('verified_by_name', 100)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->uuid('pimpinan_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            $table->foreign('generated_tax_id')->references('id')->on('taxes')->nullOnDelete();
            $table->foreign('parent_letter_id')->references('id')->on('tax_assessment_letters')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('pimpinan_id')->references('id')->on('pimpinan')->nullOnDelete();
        });

        Schema::create('tax_assessment_compensations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tax_assessment_letter_id')->index();
            $table->uuid('target_tax_id')->index();
            $table->uuid('tax_payment_id')->nullable()->index();
            $table->text('allocation_amount');
            $table->text('principal_allocated')->nullable();
            $table->text('penalty_allocated')->nullable();
            $table->timestamp('allocated_at');
            $table->uuid('allocated_by')->nullable();
            $table->string('allocated_by_name', 100)->nullable();
            $table->timestamps();

            $table->foreign('tax_assessment_letter_id')->references('id')->on('tax_assessment_letters')->cascadeOnDelete();
            $table->foreign('target_tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            $table->foreign('tax_payment_id')->references('id')->on('tax_payments')->nullOnDelete();
            $table->foreign('allocated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_assessment_compensations');
        Schema::dropIfExists('tax_assessment_letters');
    }
};