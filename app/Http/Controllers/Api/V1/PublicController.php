<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\CMS\Models\News;
use App\Domain\CMS\Models\Destination;
use Illuminate\Http\Request;

class PublicController extends BaseController
{
    /**
     * Get News List
     */
    public function getNews()
    {
        $news = News::published()
            ->latest()
            ->paginate(10); // Pagination for mobile

        return $this->sendResponse($news, 'Berita Terbaru.');
    }

    /**
     * Get Destinations
     */
    public function getDestinations(Request $request)
    {
        $query = Destination::query();

        if ($request->filled('category')) {
            $query->category($request->input('category'));
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $destinations = $query->latest()
            ->paginate(12);

        $destinations->getCollection()->transform(function ($dest) {
            $dest->makeHidden(['phone']);
            return $dest;
        });

        return $this->sendResponse($destinations, 'Destinasi Wisata.');
    }
}
