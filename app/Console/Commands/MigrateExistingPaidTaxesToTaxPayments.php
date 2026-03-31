<?php

namespace App\Console\Commands;

use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use App\Domain\Tax\Models\TaxPayment;
use Illuminate\Console\Command;

class MigrateExistingPaidTaxesToTaxPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tax:migrate-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing paid taxes to tax_payments table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Tax::where('status', TaxStatus::Paid)
            ->doesntHave('payments');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No taxes to migrate.');
            return;
        }

        $this->info("Migrating {$count} paid taxes...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->chunk(100, function ($taxes) use ($bar) {
            foreach ($taxes as $tax) {
                // Accessors automatically decrypt, mutators automatically encrypt
                // amount = principal
                // sanksi = penalty
                $principal = (float) $tax->amount;
                $penalty = (float) $tax->sanksi;
                $total = $principal + $penalty;

                TaxPayment::create([
                    'tax_id' => $tax->id,
                    'external_ref' => 'MIGRATION-' . time(),
                    'amount_paid' => $total,
                    'principal_paid' => $principal,
                    'penalty_paid' => $penalty,
                    'payment_channel' => $tax->payment_channel ?? 'MANUAL/LEGACY',
                    'paid_at' => $tax->paid_at ?? $tax->updated_at,
                    'description' => 'Migrasi Data Lama',
                    'raw_response' => ['note' => 'Generated from migration command'],
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Migration completed successfully.');
    }
}
