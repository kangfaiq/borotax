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
        Schema::table('tax_payments', function (Blueprint $table) {
            $table->text('attachment_url')->nullable()->after('description')
                  ->comment('URL/Path file bukti pembayaran fisik (terenkripsi)');
            $table->string('cancelled_reason')->nullable()->after('attachment_url')
                  ->comment('Alasan pembatalan pembayaran');
            $table->uuid('cancelled_by')->nullable()->after('cancelled_reason')
                  ->comment('User ID yang membatalkan (admin)');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_payments', function (Blueprint $table) {
            $table->dropColumn(['attachment_url', 'cancelled_reason', 'cancelled_by']);
            $table->dropSoftDeletes();
        });
    }
};
