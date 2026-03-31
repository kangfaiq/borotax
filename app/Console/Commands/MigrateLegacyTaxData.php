<?php

namespace App\Console\Commands;

use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Enums\TaxStatus;
use App\Domain\Shared\Traits\CalculatesJatuhTempo;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxPayment;
use Carbon\Carbon;

class MigrateLegacyTaxData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tax:migrate-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from legacy old app database into Borotax DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting legacy data migration...');

        // TODO: Ganti nama koneksi database menjadi nama koneksi ke database lama Anda,
        // misal didefinisikan di config/database.php sebagai 'mysql_legacy'
        // $legacyData = DB::connection('mysql_legacy')->table('t_ketetapan_pajak')
        //                  ->where('tahun_pajak', '<=', 2023)
        //                  ->get();

        // ----------------------------------------------------
        // MOCKUP DATA SEMENTARA (Contoh format dari app lama)
        // ----------------------------------------------------
        $legacyData = [
            (object) [
                'id_trx'         => 'TRX-OLD-001',
                'npwpd'          => 'P352210000001',
                'nop'            => '35221010001',
                'id_wajib_pajak' => 'WP001', // Sesuaikan
                'kode_pajak'     => '41101', // Hotel
                'sub_kode_pajak' => '4110101', // Bintang 1
                'bulan_pajak'    => 11,
                'tahun_pajak'    => 2023,
                'pokok_pajak'    => 5000000,
                'sanksi_denda'   => 100000, // Misal denda bulan Nov (2% / bulan)
                'status_bayar'   => 'BELUM LUNAS', // 'LUNAS' atau 'BELUM LUNAS'
                'tgl_bayar'      => null,
                'kode_billing'   => '352210100023456701',
            ],
            // Tambahkan baris data lainnya
        ];

        if (count($legacyData) === 0) {
            $this->info('No legacy data to migrate.');
            return;
        }

        $bar = $this->output->createProgressBar(count($legacyData));
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($legacyData as $oldData) {
                // 1. CARI RELASI UTAMA Wajib Pajak & Tax Object
                // Anda perlu implement mapping pencarian ID berdasarkan NOP / NPWPD di Borotax
                $wp = WajibPajak::where('npwpd', $oldData->npwpd)->first();
                $taxObject = TaxObject::where('nopd', $oldData->nop)->first();
                $jenisPajak = JenisPajak::where('kode', $oldData->kode_pajak)->first();
                $subJenisPajak = SubJenisPajak::where('kode', $oldData->sub_kode_pajak)->first();

                if (!$wp || !$taxObject || !$jenisPajak || !$subJenisPajak) {
                    $this->warn("\n[SKIP] Referential data not found for NPWPD/NOP: {$oldData->npwpd} / {$oldData->nop}");
                    $bar->advance();
                    continue;
                }

                // Cek agar tidak terduplikasi oleh migrasi
                $existingTax = Tax::where('legacy_billing_code', $oldData->kode_billing)->first();
                if ($existingTax) {
                    $bar->advance();
                    continue;
                }

                $statusBorotax = ($oldData->status_bayar === 'LUNAS') ? TaxStatus::Paid : TaxStatus::Pending;
                $jatuhTempo = CalculatesJatuhTempo::hitungJatuhTempoSelfAssessment(
                    $oldData->bulan_pajak, 
                    $oldData->tahun_pajak
                );

                // 2. INSERT KE TABEL TAXES (Utama)
                // Menyimpan data final tanpa dikalkulasi on-the-fly untuk sanksi
                $newTax = Tax::create([
                    'jenis_pajak_id'      => $jenisPajak->id,
                    'sub_jenis_pajak_id'  => $subJenisPajak->id,
                    'tax_object_id'       => $taxObject->id,
                    'user_id'             => $wp->user_id,
                    
                    'amount'              => (float) $oldData->pokok_pajak,
                    'omzet'               => (float) $oldData->pokok_pajak * 10, // Estimasi jika omit
                    'sanksi'              => (float) $oldData->sanksi_denda,
                    
                    'status'              => $statusBorotax,
                    'billing_code'        => Tax::generateBillingCode($jenisPajak->kode), // Generate baru jika format beda
                    
                    'payment_expired_at'  => $jatuhTempo,
                    'masa_pajak_bulan'    => $oldData->bulan_pajak,
                    'masa_pajak_tahun'    => $oldData->tahun_pajak,
                    'billing_sequence'    => 0,
                    
                    // Kolom penanda data lama
                    'is_legacy'           => true,
                    'legacy_billing_code' => $oldData->kode_billing,
                    
                    'notes'               => 'Migrasi dari Aplikasi Pajak Lama',
                    'paid_at'             => $oldData->status_bayar === 'LUNAS' && $oldData->tgl_bayar 
                                                ? Carbon::parse($oldData->tgl_bayar) : null,
                ]);

                // 3. JIKA DATA LAMA STATUSNYA "LUNAS", BUATKAN RECORD TAX_PAYMENTS
                if ($statusBorotax === TaxStatus::Paid) {
                    $totalBayar = (float) $oldData->pokok_pajak + (float) $oldData->sanksi_denda;
                    
                    TaxPayment::create([
                        'tax_id'          => $newTax->id,
                        'external_ref'    => 'MIGRATION-' . $oldData->id_trx,
                        'amount_paid'     => $totalBayar,
                        'principal_paid'  => (float) $oldData->pokok_pajak,
                        'penalty_paid'    => (float) $oldData->sanksi_denda,
                        'payment_channel' => 'MANUAL/LEGACY',
                        'paid_at'         => $oldData->tgl_bayar ? Carbon::parse($oldData->tgl_bayar) : $newTax->created_at,
                        'description'     => 'Setoran lunas tercatat di aplikasi lama',
                        'raw_response'    => ['note' => 'Generated from legacy migration'],
                    ]);
                }

                $bar->advance();
            }

            DB::commit();

            $bar->finish();
            $this->newLine();
            $this->info('Legacy Data Migration completed successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            $this->error("\nMigration failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
