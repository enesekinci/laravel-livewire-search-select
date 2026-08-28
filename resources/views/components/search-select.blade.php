@props([
    'label' => null,
    'placeholder' => 'Ara / seç...',
    'options' => [],
    'emptyLabel' => '— Seçilmedi —',
    'nullable' => true,
    'accent' => '#0b5cab',
    'teleport' => false,
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
        value: @entangle($model).live,
        options: {{ \Illuminate\Support\Js::from($normalized) }},
        emptyLabel: {{ \Illuminate\Support\Js::from($emptyLabel) }},
        placeholder: {{ \Illuminate\Support\Js::from($placeholder) }},
        nullable: {{ $nullable ? 'true' : 'false' }},
        teleport: {{ $teleport ? 'true' : 'false' }},
        panelStyle: '',
        init() {
            this._onScroll = () => { if (this.open && this.teleport) this.positionPanel() }
            window.addEventListener('scroll', this._onScroll, true)
        },
        destroy() {
            this.detachOutside()
            window.removeEventListener('scroll', this._onScroll, true)
        },
        get hasOptions() {
            return this.options.length > 0
        },
        get filtered() {
            let q = this.search.trim().toLowerCase()
            if (! q) return this.options
            return this.options.filter(o => o.label.toLowerCase().includes(q))
        },
        get selectedLabel() {
            if (this.value === null || this.value === undefined || this.value === '') {
                if (this.nullable) {
                    return this.emptyLabel
                }

                return this.options[0]?.label ?? this.placeholder
            }

            let hit = this.options.find(o => String(o.value) === String(this.value))

            if (hit) {
                return hit.label
            }

            if (this.nullable) {
                return this.emptyLabel
            }

            return String(this.value)
        },
        get valueKnown() {
            if (this.value === null || this.value === undefined || this.value === '') {
                return this.nullable || this.options.length > 0
            }

            return this.options.some(o => String(o.value) === String(this.value))
        },
        positionPanel() {
            const trigger = this.$refs.trigger
            if (! trigger) return
            const rect = trigger.getBoundingClientRect()
            this.panelStyle = `--ss-accent: {{ $accent }}; --ss-accent-soft: color-mix(in srgb, {{ $accent }} 15%, white); --ss-ring: color-mix(in srgb, {{ $accent }} 20%, transparent); position:fixed;top:${Math.round(rect.bottom + 6)}px;left:${Math.round(rect.left)}px;width:${Math.round(rect.width)}px;z-index:9998;`
        },
        select(opt) {
            this.value = opt
            this.search = ''
            this.close()
        },
        close() {
            this.open = false
            this.detachOutside()
        },
        attachOutside() {
            this.detachOutside()
            this._outside = (event) => {
                const trigger = this.$refs.trigger
                const panel = this.$refs.panel
                if (trigger?.contains(event.target) || panel?.contains(event.target)) {
                    return
                }
                this.close()
            }
            window.addEventListener('pointerdown', this._outside, true)
        },
        detachOutside() {
            if (! this._outside) return
            window.removeEventListener('pointerdown', this._outside, true)
            this._outside = null
        },
        toggle() {
            if (! this.hasOptions) return

            if (this.open) {
                this.close()
                return
            }

            if (this.teleport) {
                this.positionPanel()
            }

            this.open = true
            this.attachOutside()
            this.$nextTick(() => {
                if (this.teleport) {
                    this.positionPanel()
                }
                this.$refs.q?.focus()
            })
        }
    }"
    @keydown.escape.window="close()"
    @resize.window="open && teleport && positionPanel()"
    {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative block space-y-1.5']) }}
>
    @if ($label)
        <span class="text-sm font-medium text-slate-600">{{ $label }}</span>
    @endif

    <div class="relative">
        <button
            type="button"
            x-ref="trigger"
            @click.stop="toggle()"
            :disabled="! hasOptions"
            class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-left text-sm shadow-sm transition focus:outline-none disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400"
            :class="[
                hasOptions ? 'cursor-pointer' : 'cursor-not-allowed',
                open ? 'border-[var(--ss-accent)] ring-4 ring-[var(--ss-ring)]' : 'focus:border-[var(--ss-accent)] focus:ring-4 focus:ring-[var(--ss-ring)]',
                ! valueKnown && hasOptions ? 'border-amber-300' : '',
            ]"
        >
            <span
                class="truncate"
                :class="(value === null || value === undefined || value === '') ? 'text-slate-400' : (valueKnown ? 'text-slate-900' : 'text-amber-700')"
                x-text="hasOptions ? selectedLabel : placeholder"
            ></span>
            <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        @if ($teleport)
            <template x-teleport="document.body">
                <div
                    x-ref="panel"
                    x-show="open"
                    x-cloak
                    x-transition.opacity.duration.100ms
                    :style="panelStyle"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-900/10"
                >
                    @include('search-select::components.partials.dropdown-panel')
                </div>
            </template>
        @else
            <div
                x-ref="panel"
                x-show="open"
                x-cloak
                x-transition.opacity.duration.100ms
                class="absolute z-[9998] mt-1.5 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-900/10"
            >
                @include('search-select::components.partials.dropdown-panel')
            </div>
        @endif
    </div>

    <p x-show="! hasOptions" x-cloak class="text-xs text-amber-700">Seçenek listesi boş.</p>
    <p x-show="hasOptions && ! valueKnown && (value !== null && value !== undefined && value !== '')" x-cloak class="text-xs text-amber-700">Kayıtlı değer listede yok; yeni bir seçim yapın.</p>
</div>
