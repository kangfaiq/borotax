<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Enums\TaxStatus;

class DashboardController extends Controller
{
    public function index()
    {
        Tax::syncExpiredStatuses();

        /** @var \App\Domain\Auth\Models\User $user */
        $user = auth('portal')->user();
        $wajibPajak = $user->wajibPajak;

        $pendingTaxes = Tax::where('user_id', $user->id)
            ->whereIn('status', [
                TaxStatus::Pending,
                TaxStatus::PartiallyPaid,
                TaxStatus::Expired,
            ])
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingAmount = (float) $pendingTaxes->sum(function (Tax $tax): float {
            return $tax->getRemainingAmount();
        });

        $paidTaxes = Tax::where('user_id', $user->id)
            ->whereIn('status', [TaxStatus::Paid, TaxStatus::Verified])
            ->with('payments')
            ->get();

        $paidAmount = (float) $paidTaxes->sum(function (Tax $tax): float {
            $totalPaid = $tax->getTotalPaid();

            if ($totalPaid > 0) {
                return $totalPaid;
            }

            return (float) $tax->amount + (float) $tax->sanksi;
        });

        $taxObjectsCount = $this->resolveTaxObjectsQuery($user->nik_hash, $wajibPajak?->npwpd)->count();

        $recentTransactions = Tax::where('user_id', $user->id)
            ->with(['jenisPajak', 'taxObject', 'children:id,parent_tax_id'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalCoupons = $user->total_kupon_undian ?? 0;

        return view('portal.dashboard', compact(
            'user',
            'pendingTaxes',
            'pendingAmount',
            'paidAmount',
            'taxObjectsCount',
            'recentTransactions',
            'totalCoupons'
        ));
    }

    private function resolveTaxObjectsQuery(?string $nikHash, ?string $npwpd): \Illuminate\Database\Eloquent\Builder
    {
        $query = TaxObject::query();

        if ($npwpd) {
            return $query->where('npwpd', $npwpd);
        }

        return $query->where('nik_hash', $nikHash);
    }
}
