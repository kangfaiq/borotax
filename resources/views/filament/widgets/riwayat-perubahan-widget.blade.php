<x-filament-widgets::widget>
    <x-filament::section heading="Riwayat Perubahan Data" collapsible collapsed>
        @include('filament.components.riwayat-perubahan', [
            'activityLogs' => $activityLogs,
            'changeRequests' => $changeRequests,
        ])
    </x-filament::section>
</x-filament-widgets::widget>
