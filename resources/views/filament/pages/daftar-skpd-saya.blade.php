<x-filament-panels::page>

    {{-- Tab Jenis SKPD --}}
    <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1 gap-1">
        <button wire:click="setJenisSkpd('air_tanah')"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-all
                    {{ $jenisSkpd === 'air_tanah'
                        ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            SKPD Air Tanah
        </button>
        <button wire:click="setJenisSkpd('reklame')"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-all
                    {{ $jenisSkpd === 'reklame'
                        ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            SKPD Reklame
        </button>
    </div>

    {{-- Filament Table --}}
    {{ $this->table }}

</x-filament-panels::page>
