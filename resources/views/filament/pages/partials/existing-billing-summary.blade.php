<div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/70">
    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Billing Sumber</p>
    <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
        <div>
            <p class="text-slate-500 dark:text-slate-400">Billing Sumber</p>
            <p class="font-semibold text-slate-900 dark:text-white">{{ $existingBillingInfo['billing_code'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-slate-500 dark:text-slate-400">Status</p>
            <p class="font-semibold text-slate-900 dark:text-white">{{ $existingBillingInfo['status_label'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-slate-500 dark:text-slate-400">Masa Pajak</p>
            <p class="font-semibold text-slate-900 dark:text-white">{{ $existingBillingInfo['period_label'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-slate-500 dark:text-slate-400">Nominal Pajak</p>
            <p class="font-semibold text-slate-900 dark:text-white">{{ $existingBillingInfo['amount_label'] ?? '-' }}</p>
        </div>
    </div>
</div>