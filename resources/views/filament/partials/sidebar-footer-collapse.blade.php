<div
    x-data="{}"
    x-show="$store.sidebar.isOpen"
    x-transition:enter="lg:transition lg:delay-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-cloak
    class="fi-sidebar-footer-collapse hidden lg:flex items-center px-4 py-3"
>
    <button
        type="button"
        x-on:click="$store.sidebar.close()"
        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition duration-75"
    >
        <span class="text-xs font-medium">Collapse</span>
        <x-filament::icon
            icon="heroicon-o-chevron-double-left"
            class="h-4 w-4 rtl:rotate-180"
        />
    </button>
</div>
