<?php

namespace App\Http\Controllers;

use App\Domain\Tax\Models\PortalMblbSubmission;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PortalMblbSubmissionAttachmentController extends Controller
{
    public function __invoke(string $submissionId): BinaryFileResponse
    {
        $submission = PortalMblbSubmission::findOrFail($submissionId);
        $user = auth('web')->user() ?? auth('portal')->user();

        abort_unless($user, 404);

        if (! $user->hasRole(['admin', 'verifikator', 'petugas'])) {
            abort_unless((string) $submission->user_id === (string) $user->id, 403);
        }

        abort_unless(
            filled($submission->attachment_path) && Storage::disk('public')->exists($submission->attachment_path),
            404,
        );

        return response()->file(Storage::disk('public')->path($submission->attachment_path));
    }
}