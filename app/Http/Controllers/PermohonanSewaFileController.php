<?php

namespace App\Http\Controllers;

use App\Domain\Reklame\Models\PermohonanSewaReklame;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PermohonanSewaFileController extends Controller
{
    public function __invoke(string $id, string $field): BinaryFileResponse
    {
        abort_unless(in_array($field, ['file_ktp', 'file_npwp', 'file_desain_reklame'], true), 404);

        $permohonan = PermohonanSewaReklame::findOrFail($id);

        $this->authorizeFileAccess($permohonan);

        $path = $permohonan->{$field};

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    private function authorizeFileAccess(PermohonanSewaReklame $permohonan): void
    {
        $user = auth()->user();

        abort_if(
            ! $user || (! $user->hasRole(['admin', 'verifikator', 'petugas']) && $permohonan->user_id !== $user->id),
            404
        );
    }
}