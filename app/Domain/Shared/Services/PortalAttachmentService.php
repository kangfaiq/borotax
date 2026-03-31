<?php

namespace App\Domain\Shared\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PortalAttachmentService
{
    public function storeMblbSupportingDocument(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType() ?? '';

        if (str_starts_with($mimeType, 'image/')) {
            return $this->compressImageToWebp($file);
        }

        if ($file->getSize() > 1024 * 1024) {
            throw ValidationException::withMessages([
                'attachment' => 'Ukuran file PDF maksimal 1MB.',
            ]);
        }

        return $file->store('portal-mblb-submissions/attachments', 'public');
    }

    private function compressImageToWebp(UploadedFile $file): string
    {
        $sourcePath = $file->getRealPath();
        $mimeType = $file->getMimeType();

        $sourceImage = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (! $sourceImage) {
            throw ValidationException::withMessages([
                'attachment' => 'Format gambar tidak didukung.',
            ]);
        }

        $directory = 'portal-mblb-submissions/attachments';
        $filename = $directory . '/' . Str::random(40) . '.webp';
        $storagePath = Storage::disk('public')->path($filename);

        if (! is_dir(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        $current = $sourceImage;
        $currentWidth = imagesx($current);
        $currentHeight = imagesy($current);
        $maxSize = 1024 * 1024;

        while (true) {
            $quality = 85;

            do {
                imagewebp($current, $storagePath, $quality);
                clearstatcache(true, $storagePath);
                $size = filesize($storagePath);
                $quality -= 10;
            } while ($size > $maxSize && $quality >= 25);

            if ($size <= $maxSize) {
                break;
            }

            if ($currentWidth <= 600 || $currentHeight <= 600) {
                imagedestroy($sourceImage);
                if ($current !== $sourceImage) {
                    imagedestroy($current);
                }

                throw ValidationException::withMessages([
                    'attachment' => 'Gambar tidak dapat dikompres hingga maksimal 1MB. Silakan gunakan gambar dengan resolusi lebih kecil.',
                ]);
            }

            $nextWidth = max((int) round($currentWidth * 0.85), 600);
            $nextHeight = max((int) round($currentHeight * 0.85), 600);
            $resized = imagecreatetruecolor($nextWidth, $nextHeight);

            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $current, 0, 0, 0, 0, $nextWidth, $nextHeight, $currentWidth, $currentHeight);

            if ($current !== $sourceImage) {
                imagedestroy($current);
            }

            $current = $resized;
            $currentWidth = $nextWidth;
            $currentHeight = $nextHeight;
        }

        imagedestroy($sourceImage);
        if ($current !== $sourceImage) {
            imagedestroy($current);
        }

        return $filename;
    }
}