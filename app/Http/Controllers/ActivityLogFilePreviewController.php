<?php

namespace App\Http\Controllers;

use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Shared\Models\ActivityLog;
use App\Domain\Tax\Models\TaxObject;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivityLogFilePreviewController extends Controller
{
    private const PREVIEWABLE_FIELDS = [
        'foto_objek_path' => 'public',
        'file_desain_reklame' => 'local',
    ];

    public function __invoke(ActivityLog $activityLog, string $version, string $field): BinaryFileResponse
    {
        abort_unless(in_array($version, ['old', 'new'], true), 404);
        abort_unless(array_key_exists($field, self::PREVIEWABLE_FIELDS), 404);

        $this->authorizePreviewAccess($activityLog);

        $values = $version === 'old' ? $activityLog->old_values : $activityLog->new_values;
        $path = data_get($values, $field);
        $disk = self::PREVIEWABLE_FIELDS[$field];

        abort_unless(filled($path) && Storage::disk($disk)->exists($path), 404);

        return response()->file(Storage::disk($disk)->path($path));
    }

    private function authorizePreviewAccess(ActivityLog $activityLog): void
    {
        $user = auth()->user();

        abort_if(! $user, 404);

        if ($user->hasRole(['admin', 'verifikator', 'petugas'])) {
            return;
        }

        match ($activityLog->target_table) {
            'tax_objects' => $this->authorizeTaxObjectPreview($user, (string) $activityLog->target_id),
            'permohonan_sewa_reklame' => $this->authorizePermohonanPreview($user, (string) $activityLog->target_id),
            default => abort(404),
        };
    }

    private function authorizeTaxObjectPreview($user, string $targetId): void
    {
        abort_unless(
            TaxObject::query()
                ->where('id', $targetId)
                ->where('nik_hash', TaxObject::generateHash((string) $user->nik))
                ->exists(),
            404,
        );
    }

    private function authorizePermohonanPreview($user, string $targetId): void
    {
        abort_unless(
            PermohonanSewaReklame::query()
                ->where('id', $targetId)
                ->where('user_id', $user->id)
                ->exists(),
            404,
        );
    }
}