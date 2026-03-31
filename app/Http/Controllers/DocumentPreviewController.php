<?php

namespace App\Http\Controllers;

use App\Domain\Shared\Services\DocumentPreviewService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DocumentPreviewController extends Controller
{
    public function __construct(private readonly DocumentPreviewService $documentPreviewService)
    {
    }

    public function index(): View
    {
        $this->authorizePreviewAccess();

        return view('document-previews.index', [
            'previews' => collect($this->documentPreviewService->catalog())->groupBy('category'),
        ]);
    }

    public function show(Request $request, string $preview): Response
    {
        $this->authorizePreviewAccess();

        $document = $this->documentPreviewService->make($preview);

        $pdf = Pdf::loadView($document['view'], $document['data']);
        $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

        return $request->boolean('download')
            ? $pdf->download($document['filename'])
            : $pdf->stream($document['filename']);
    }

    private function authorizePreviewAccess(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        abort_unless(auth()->user()?->hasRole('admin'), 404);
    }
}