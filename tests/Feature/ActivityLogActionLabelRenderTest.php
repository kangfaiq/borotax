<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\ActivityLog;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ActivityLogActionLabelRenderTest extends TestCase
{
    public function test_history_view_renders_human_readable_action_labels(): void
    {
        $actor = new User([
            'name' => 'Verifikator Riwayat',
        ]);

        $skpdSyncLog = new ActivityLog();
        $skpdSyncLog->forceFill([
            'action' => 'UPDATE_TAX_OBJECT_FROM_SKPD_REKLAME_APPROVAL',
            'description' => 'Sinkronisasi objek setelah persetujuan SKPD.',
            'old_values' => ['nama_objek_pajak' => 'Nama Lama'],
            'new_values' => ['nama_objek_pajak' => 'Nama Baru'],
        ]);
        $skpdSyncLog->created_at = Carbon::parse('2026-04-07 10:15:00');
        $skpdSyncLog->setRelation('actor', $actor);

        $genericLog = new ActivityLog();
        $genericLog->forceFill([
            'action' => 'APPROVE_DATA_CHANGE',
            'description' => 'Permintaan perubahan disetujui.',
            'old_values' => ['alamat_objek' => 'Alamat Lama'],
            'new_values' => ['alamat_objek' => 'Alamat Baru'],
        ]);
        $genericLog->created_at = Carbon::parse('2026-04-07 11:00:00');
        $genericLog->setRelation('actor', $actor);

        $html = view('filament.components.riwayat-perubahan', [
            'activityLogs' => collect([$skpdSyncLog, $genericLog]),
            'changeRequests' => collect(),
        ])->render();

        $this->assertStringContainsString('Sinkronisasi Objek dari Persetujuan SKPD Reklame', $html);
        $this->assertStringContainsString('Approve Data Change', $html);
        $this->assertStringNotContainsString('UPDATE_TAX_OBJECT_FROM_SKPD_REKLAME_APPROVAL', $html);
        $this->assertStringNotContainsString('APPROVE_DATA_CHANGE', $html);
    }
}
