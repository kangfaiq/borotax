<?php

namespace App\Http\Controllers;

use App\Domain\CMS\Models\News;
use App\Domain\CMS\Models\Destination;
use App\Domain\WajibPajak\Models\WajibPajak;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $news = News::published()
            ->latestPublished()
            ->take(3)
            ->get();

        $destinations = Destination::latest()
            ->take(6)
            ->get();

        // Stats for landing page counters
        $totalWp = WajibPajak::where('status', 'disetujui')->count();
        $totalDestinations = Destination::count();

        return view('landing', compact('news', 'destinations', 'totalWp', 'totalDestinations'));
    }
}
