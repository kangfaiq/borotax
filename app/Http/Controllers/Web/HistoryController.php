<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Tax\Models\Tax;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * Show transaction history for the authenticated user.
     */
    public function transactions(Request $request)
    {
        $user = auth()->user();

        $query = Tax::where('user_id', $user->id)
            ->with(['jenisPajak', 'parent', 'children']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by billing code or jenis pajak
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('billing_code', 'like', "%{$search}%")
                  ->orWhereHas('jenisPajak', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = in_array($request->input('per_page'), [10, 15, 25, 50]) 
            ? (int) $request->input('per_page') 
            : 15;

        $transactions = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('portal.history.transactions', compact('transactions'));
    }
}
