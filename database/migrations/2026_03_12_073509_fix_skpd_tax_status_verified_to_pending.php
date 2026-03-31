<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix: SKPD taxes yang status 'verified' dan sudah ada pembayaran di tax_payments
     * harus diubah ke 'paid' (karena sebelumnya tidak berubah ke 'paid' saat dibayar).
     * 
     * SKPD taxes yang 'verified' tapi belum bayar tetap 'verified' (menunggu pembayaran).
     */
    public function up(): void
    {
        // Ubah verified → paid untuk SKPD taxes yang sudah ada pembayaran
        DB::table('taxes')
            ->where('status', 'verified')
            ->whereNotNull('skpd_number')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tax_payments')
                    ->whereColumn('tax_payments.tax_id', 'taxes.id');
            })
            ->update([
                'status' => 'paid',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak bisa di-reverse secara akurat
    }
};
