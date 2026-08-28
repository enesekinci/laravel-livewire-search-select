<div class="border-b border-slate-100 p-2">
    <input
        x-ref="q"
        type="search"
        x-model="search"
        @keydown.enter.prevent="filtered[0] && select(filtered[0].value)"
        placeholder="{{ $placeholder }}"
        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[var(--ss-accent)] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[var(--ss-ring)]"
    >
</div>

<ul class="max-h-56 overflow-y-auto py-1 text-sm">
    <li x-show="nullable">
        <button
            type="button"
            @click="select(null)"
            class="flex w-full cursor-pointer px-3 py-2 text-left text-slate-500 hover:bg-slate-50"
            :class="(value === null || value === '') && 'bg-[var(--ss-accent-soft)] text-[var(--ss-accent)]'"
            x-text="emptyLabel"
        ></button>
    </li>

    <template x-for="opt in filtered" :key="opt.value">
        <li>
            <button
                type="button"
                @click="select(opt.value)"
                class="flex w-full cursor-pointer px-3 py-2 text-left text-slate-800 hover:bg-slate-50"
                :class="String(value) === String(opt.value) && 'bg-[var(--ss-accent-soft)] font-medium text-[var(--ss-accent)]'"
                x-text="opt.label"
            ></button>
        </li>
    </template>

    <li x-show="filtered.length === 0" class="px-3 py-3 text-slate-400">Sonuç yok</li>
</ul>
