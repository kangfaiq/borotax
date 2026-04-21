@props([
    'model' => 'instansiId',
    'options' => [],
    'selected' => null,
    'label' => 'Instansi / OPD / Lembaga',
    'helpText' => null,
    'containerClass' => 'mb-5',
    'ringClass' => 'sa-ring',
    'placeholder' => 'Cari instansi / OPD / lembaga...',
    'emptyLabel' => '-- Opsional, tanpa instansi --',
])

@php
    $normalizedOptions = collect($options)
        ->map(fn (string $optionLabel, string $optionId) => [
            'id' => $optionId,
            'label' => $optionLabel,
        ])
        ->values()
        ->all();
@endphp

<div
    x-data="{
        options: @js($normalizedOptions),
        selectedId: @js(filled($selected) ? (string) $selected : ''),
        search: '',
        open: false,
        init() {
            this.syncSearchFromSelection();
        },
        syncSearchFromSelection() {
            const selectedOption = this.options.find((option) => option.id === this.selectedId);

            if (selectedOption) {
                this.search = selectedOption.label;

                return;
            }

            if (!this.open) {
                this.search = '';
            }
        },
        normalizedQuery() {
            return this.search.trim().toLowerCase();
        },
        filteredOptions() {
            const query = this.normalizedQuery();

            if (!query) {
                return this.options.slice(0, 50);
            }

            return this.options
                .filter((option) => option.label.toLowerCase().includes(query))
                .slice(0, 50);
        },
        selectOption(option) {
            this.selectedId = option.id;
            this.search = option.label;
            this.open = false;
        },
        clearSelection() {
            this.selectedId = '';
            this.search = '';
            this.open = false;
        },
        closePicker() {
            window.setTimeout(() => {
                this.open = false;
                this.syncSearchFromSelection();
            }, 120);
        },
    }"
    class="{{ $containerClass }}"
>
    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ $label }}</label>

    <div class="relative">
        <select wire:model.live="{{ $model }}"
                x-model="selectedId"
                class="hidden"
                tabindex="-1"
                aria-hidden="true">
            <option value=""></option>
            @foreach ($options as $optionId => $optionLabel)
                <option value="{{ $optionId }}">{{ $optionLabel }}</option>
            @endforeach
        </select>

        <input type="text"
               x-model="search"
               @focus="open = true"
               @input="open = true; if ($event.target.value === '') clearSelection()"
               @keydown.escape.prevent="open = false; syncSearchFromSelection()"
               @keydown.enter.prevent="if (filteredOptions().length > 0) selectOption(filteredOptions()[0])"
               @blur="closePicker()"
               placeholder="{{ $placeholder }}"
               class="{{ $ringClass }} block w-full py-2.5 pl-3 pr-10 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500">

        <button type="button"
                x-show="selectedId"
                x-cloak
                @click="clearSelection()"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-200"
                aria-label="Hapus instansi terpilih">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div x-show="open"
             x-cloak
             class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
            <div class="max-h-64 overflow-y-auto py-1">
                <button type="button"
                        @mousedown.prevent="clearSelection()"
                        class="block w-full px-3 py-2 text-left text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                    {{ $emptyLabel }}
                </button>

                <template x-for="option in filteredOptions()" :key="option.id">
                    <button type="button"
                            @mousedown.prevent="selectOption(option)"
                            class="block w-full px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-white"
                            :class="option.id === selectedId ? 'bg-slate-100 text-slate-900 font-semibold dark:bg-slate-100 dark:text-slate-900' : ''">
                        <span x-text="option.label"></span>
                    </button>
                </template>

                <div x-show="filteredOptions().length === 0"
                     x-cloak
                     class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada instansi yang cocok dengan pencarian.
                </div>
            </div>
        </div>
    </div>

    @if ($helpText)
        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">{{ $helpText }}</p>
    @endif
</div>