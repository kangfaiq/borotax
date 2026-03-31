<?php

namespace App\Filament\Pages\Concerns;

use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;

trait InteractsWithDuplicateBillingInfo
{
    protected function buildExistingBillingInfo(Tax $existingTax, string $periodLabel): array
    {
        $status = $existingTax->status instanceof TaxStatus
            ? $existingTax->status
            : TaxStatus::from((string) $existingTax->status);

        return [
            'id' => $existingTax->id,
            'billing_code' => (string) $existingTax->billing_code,
            'omzet' => $existingTax->omzet !== null ? (float) $existingTax->omzet : null,
            'amount' => (float) $existingTax->amount,
            'amount_label' => $this->formatBillingCurrency((float) $existingTax->amount),
            'period_label' => $periodLabel,
            'pembetulan_ke' => (int) $existingTax->pembetulan_ke,
            'status' => $status->value,
            'status_label' => $status->getLabel() ?? str($status->value)->headline()->toString(),
            'is_paid' => in_array($status, [TaxStatus::Paid, TaxStatus::Verified], true),
        ];
    }

    protected function formatBillingCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}