@props([
    'label' => null,
    'placeholder' => 'Ara / seç...',
    'options' => [],
    'emptyLabel' => '— Seçilmedi —',
    'nullable' => true,
    'accent' => '#0b5cab',
])

@php
    $model = $attributes->wire('model');

    $normalized = collect($options)
        ->map(function ($option) {
            if (is_array($option)) {
                return [
                    'value' => (string) ($option['value'] ?? ''),
                    'label' => (string) ($option['label'] ?? ''),
                ];
            }

            return [
                'value' => (string) $option,
                'label' => (string) $option,
            ];
        })
        ->values()
        ->all();
@endphp

<div
    style="--ss-accent: {{ $accent }}; --ss-accent-soft: color-mix(in srgb, {{ $accent }} 15%, white); --ss-ring: color-mix(in srgb, {{ $accent }} 20%, transparent);"
    x-data="{
        open: false,
        search: '',
        value: @entangle($model),
        options: {{ \Illuminate\Support\Js::from($normalized) }},
        emptyLabel: {{ \Illuminate\Support\Js::from($emptyLabel) }},
        placeholder: {{ \Illuminate\Support\Js::from($placeholder) }},
        nullable: {{ $nullable ? 'true' : 'false' }},
        get filtered() {
            let q = this.search.trim().toLowerCase()
            if (! q) return this.options
            return this.options.filter(o => o.label.toLowerCase().includes(q))
        },
        get selectedLabel() {
            if (this.value === null || this.value === undefined || this.value === '') {
                return this.nullable ? this.emptyLabel : (this.options[0]?.label ?? this.placeholder)
            }
            let hit = this.options.find(o => String(o.value) === String(this.value))
            return hit ? hit.label : (this.nullable ? this.emptyLabel : this.placeholder)
        },
        select(opt) {
            this.value = opt
            this.search = ''
            this.open = false
        },
        toggle() {
            this.open = ! this.open
            if (this.open) {
                this.$nextTick(() => this.$refs.q?.focus())
            }
        }
    }"
    @keydown.escape.window="open = false"
    {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative block space-y-1.5']) }}
>
    @if ($label)
        <span class="text-sm font-medium text-slate-600">{{ $label }}</span>
    @endif

    <div class="relative" @click.outside="open = false">
        <button
            type="button"
            @click="toggle()"
            class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-left text-sm shadow-sm transition focus:outline-none"
            :class="open ? 'border-[var(--ss-accent)] ring-4 ring-[var(--ss-ring)]' : 'focus:border-[var(--ss-accent)] focus:ring-4 focus:ring-[var(--ss-ring)]'"
        >
            <span class="truncate" :class="(value === null || value === '') ? 'text-slate-400' : 'text-slate-900'" x-text="selectedLabel"></span>
            <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.opacity.duration.100ms
            class="absolute z-40 mt-1.5 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-900/10"
        >
            <div class="border-b border-slate-100 p-2">
                <input
                    x-ref="q"
                    type="search"
                    x-model="search"
                    @keydown.enter.prevent="filtered[0] && select(filtered[0].value)"
                    placeholder="{{ $placeholder }}"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:border-[var(--ss-accent)] focus:ring-[var(--ss-ring)]"
                >
            </div>

            <ul class="max-h-56 overflow-y-auto py-1 text-sm">
                <template x-if="nullable">
                    <li>
                        <button
                            type="button"
                            @click="select(null)"
                            class="flex w-full px-3 py-2 text-left text-slate-500 hover:bg-slate-50"
                            :class="(value === null || value === '') && 'bg-[var(--ss-accent-soft)] text-[var(--ss-accent)]'"
                            x-text="emptyLabel"
                        ></button>
                    </li>
                </template>

                <template x-for="opt in filtered" :key="opt.value">
                    <li>
                        <button
                            type="button"
                            @click="select(opt.value)"
                            class="flex w-full px-3 py-2 text-left text-slate-800 hover:bg-slate-50"
                            :class="String(value) === String(opt.value) && 'bg-[var(--ss-accent-soft)] font-medium text-[var(--ss-accent)]'"
                            x-text="opt.label"
                        ></button>
                    </li>
                </template>

                <li x-show="filtered.length === 0" class="px-3 py-3 text-slate-400">Sonuç yok</li>
            </ul>
        </div>
    </div>
</div>
