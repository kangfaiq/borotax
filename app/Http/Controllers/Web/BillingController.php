<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Tax\Models\Tax;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Show billing check form / result.
     */
    public function check(Request $request)
    {
        $billing = null;
        $code = $request->input('code');

        if ($code) {
            $billing = Tax::wherePaymentCode($code)
                ->with(['jenisPajak', 'children:id,parent_tax_id', 'taxObject'])
                ->first();
        }

        return view('portal.billing.check', compact('billing', 'code'));
    }

    /**
     * Show billing check inside the authenticated portal (sidebar layout).
     */
    public function portalCheck(Request $request)
    {
        $billing = null;
        $code = $request->input('code');

        if ($code) {
            $billing = Tax::wherePaymentCode($code)
                ->with(['jenisPajak', 'children:id,parent_tax_id', 'taxObject'])
                ->first();
        }

        return view('portal.billing.portal-check', compact('billing', 'code'));
    }
}
