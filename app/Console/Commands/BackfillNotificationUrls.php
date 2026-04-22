<?php

namespace App\Console\Commands;

use App\Domain\Shared\Models\Notification;
use Illuminate\Console\Command;

class BackfillNotificationUrls extends Command
{
    protected $signature = 'notifications:backfill-urls {--dry-run : Hanya tampilkan jumlah yang akan diupdate, tanpa menyimpan}';

    protected $description = 'Set data_payload.url pada notifikasi portal lama berdasarkan pola judul (best-effort).';

    private const TITLE_URL_MAP = [
        'pembetulan billing'       => 'portal.pembetulan.index',
        'surat ketetapan pajak'    => 'portal.history',
        'kompensasi skpdlb'        => 'portal.history',
        'billing mblb'             => 'portal.history',
        'gebyar'                   => 'portal.dashboard',
        'pengajuan reklame'        => 'portal.reklame.index',
        'permohonan sewa reklame'  => 'portal.dashboard',
        'laporan meter'            => 'portal.air-tanah.skpd-list',
        'perubahan data'           => 'portal.dashboard',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $candidates = Notification::query()
            ->where(function ($query) {
                $query->whereNull('data_payload')
                    ->orWhereRaw("JSON_EXTRACT(data_payload, '$.url') IS NULL");
            })
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Tidak ada notifikasi yang perlu di-backfill.');
            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($candidates as $notification) {
            $url = $this->resolveUrl($notification->title);

            if (! $url) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $updated++;
                continue;
            }

            $payload = $notification->data_payload ?? [];
            $payload['url'] = $url;
            $notification->data_payload = $payload;
            $notification->saveQuietly();
            $updated++;
        }

        $action = $dryRun ? 'akan diupdate' : 'diupdate';
        $this->info("{$updated} notifikasi {$action}, {$skipped} dilewati (judul tidak cocok pola).");

        return self::SUCCESS;
    }

    private function resolveUrl(?string $title): ?string
    {
        if (blank($title)) {
            return null;
        }

        $titleLower = mb_strtolower($title);

        foreach (self::TITLE_URL_MAP as $needle => $routeName) {
            if (str_contains($titleLower, $needle)) {
                try {
                    return route($routeName);
                } catch (\Throwable) {
                    return null;
                }
            }
        }

        return null;
    }
}
