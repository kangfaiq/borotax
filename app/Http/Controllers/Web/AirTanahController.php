<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use Illuminate\Http\Request;

class AirTanahController extends Controller
{
    /* ======================================================
     * Hub / Index — ringkasan layanan air tanah
     * ====================================================== */
    public function index()
    {
        $user = auth()->user();
        $nikHash = WaterObject::generateHash($user->nik);

        $totalObjek = WaterObject::where('nik_hash', $nikHash)->active()->count();

        $perluLapor = WaterObject::where('nik_hash', $nikHash)
            ->active()
            ->where(function ($q) {
                $q->whereNull('last_report_date')
                  ->orWhere('last_report_date', '<', now()->startOfMonth());
            })
            ->count();

        $skpdCount = SkpdAirTanah::whereHas('waterObject', function ($q) use ($nikHash) {
            $q->where('nik_hash', $nikHash);
        })->count();

        return view('portal.air-tanah.index', compact('totalObjek', 'perluLapor', 'skpdCount'));
    }

    /* ======================================================
     * Daftar objek air tanah milik WP
     * ====================================================== */
    public function objects()
    {
        $user = auth()->user();
        $nikHash = WaterObject::generateHash($user->nik);

        $objects = WaterObject::where('nik_hash', $nikHash)
            ->active()
            ->with(['meterReports' => fn ($q) => $q->latest('reported_at')->limit(1)])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.air-tanah.objects', compact('objects'));
    }

    /* ======================================================
     * Daftar SKPD Air Tanah
     * ====================================================== */
    public function skpdList(Request $request)
    {
        $user = auth()->user();
        $nikHash = WaterObject::generateHash($user->nik);

        $tab = $request->get('tab', 'selesai');

        $query = SkpdAirTanah::whereHas('waterObject', function ($q) use ($nikHash) {
            $q->where('nik_hash', $nikHash);
        })->with(['waterObject']);

        if ($tab === 'proses') {
            $query->whereIn('status', ['draft', 'menungguVerifikasi']);
        } else {
            $query->whereIn('status', ['disetujui', 'ditolak']);
        }

        $skpds = $query->orderBy('created_at', 'desc')->get();

        return view('portal.air-tanah.skpd-list', compact('skpds', 'tab'));
    }

    /* ======================================================
     * Detail SKPD Air Tanah
     * ====================================================== */
    public function skpdDetail(string $skpdId)
    {
        $user = auth()->user();
        $nikHash = WaterObject::generateHash($user->nik);

        $skpd = SkpdAirTanah::whereHas('waterObject', function ($q) use ($nikHash) {
            $q->where('nik_hash', $nikHash);
        })->with(['waterObject', 'meterReport'])
          ->findOrFail($skpdId);

        return view('portal.air-tanah.skpd-detail', compact('skpd'));
    }
}
