<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Enums\TaxStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userNikHash = $user->nik_hash;

        // Pending taxes / billing
        $pendingTaxes = Tax::where('user_id', $user->id)
            ->where('status', TaxStatus::Pending)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pendingAmount = Tax::where('user_id', $user->id)
            ->where('status', TaxStatus::Pending)
            ->sum('amount');

        // Total paid taxes
        $paidAmount = Tax::where('user_id', $user->id)
            ->where('status', TaxStatus::Paid)
            ->sum('amount');

        // Tax objects count (unified)
        $taxObjectsCount = TaxObject::where('nik_hash', $userNikHash)->count();

        // Recent transactions (last 5)
        $recentTransactions = Tax::where('user_id', $user->id)
            ->with(['jenisPajak', 'taxObject'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Gebyar coupons
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
}
