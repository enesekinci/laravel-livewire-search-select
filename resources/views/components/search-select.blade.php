@props([
    'label' => null,
    'placeholder' => 'Ara / seç...',
    'options' => [],
    'emptyLabel' => '— Seçilmedi —',
    'emptyOptionsLabel' => 'Seçenek listesi boş',
    'nullable' => true,
    'accent' => '#0b5cab',
    'teleport' => true,
    'disabled' => false,
])

@php
    $model = $attributes->wire('model');

    $normalized = collect($options)
        ->map(function ($option) {
            if (is_array($option)) {
                $value = $option['value'] ?? $option['id'] ?? '';
                $label = $option['label'] ?? $option['name'] ?? '';

                return [
                    'value' => (string) $value,
                    'label' => (string) $label,
                ];
            }

            if (is_object($option)) {
                return [
                    'value' => (string) ($option->value ?? $option->id ?? ''),
                    'label' => (string) ($option->label ?? $option->name ?? ''),
                ];
            }

            return [
                'value' => (string) $option,
                'label' => (string) $option,
            ];
        })
        ->filter(fn (array $option) => $option['value'] !== '' || $option['label'] !== '')
        ->values()
        ->all();

    $optionsKey = md5(json_encode($normalized) ?: '');
@endphp

<div
    wire:key="search-select-{{ $model }}-{{ $optionsKey }}"
    style="--ss-accent: {{ $accent }}; --ss-accent-soft: color-mix(in srgb, {{ $accent }} 15%, white); --ss-ring: color-mix(in srgb, {{ $accent }} 20%, transparent);"
    x-data="{
        open: false,
        search: '',
        value: @entangle($model).live,
        options: {{ \Illuminate\Support\Js::from($normalized) }},
        emptyLabel: {{ \Illuminate\Support\Js::from($emptyLabel) }},
        emptyOptionsLabel: {{ \Illuminate\Support\Js::from($emptyOptionsLabel) }},
        placeholder: {{ \Illuminate\Support\Js::from($placeholder) }},
        nullable: {{ $nullable ? 'true' : 'false' }},
        teleport: {{ $teleport ? 'true' : 'false' }},
        disabled: {{ $disabled ? 'true' : 'false' }},
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
            return Array.isArray(this.options) && this.options.length > 0
        },
        get canOpen() {
            return this.hasOptions && ! this.disabled
        },
        get filtered() {
            let q = this.search.trim().toLowerCase()
            if (! q) return this.options
            return this.options.filter(o => String(o.label).toLowerCase().includes(q))
        },
        get selectedLabel() {
            if (this.value === null || this.value === undefined || this.value === '') {
                if (! this.hasOptions) {
                    return this.emptyOptionsLabel
                }

                return this.nullable ? this.emptyLabel : (this.placeholder || this.options[0]?.label || this.emptyLabel)
            }

            let hit = this.options.find(o => String(o.value) === String(this.value))

            return hit ? hit.label : (this.hasOptions ? String(this.value) : this.emptyOptionsLabel)
        },
        positionPanel() {
            const trigger = this.$refs.trigger
            if (! trigger) return
            const rect = trigger.getBoundingClientRect()
            this.panelStyle = `position:fixed;top:${Math.round(rect.bottom + 6)}px;left:${Math.round(rect.left)}px;width:${Math.round(rect.width)}px;z-index:9998;`
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
            if (! this.canOpen) {
                return
            }

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
            :disabled="! canOpen"
            :aria-expanded="open.toString()"
            :title="! hasOptions ? emptyOptionsLabel : placeholder"
            class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-left text-sm shadow-sm transition focus:outline-none disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400"
            :class="[
                canOpen ? 'cursor-pointer' : 'cursor-not-allowed',
                open ? 'border-[var(--ss-accent)] ring-4 ring-[var(--ss-ring)]' : 'focus:border-[var(--ss-accent)] focus:ring-4 focus:ring-[var(--ss-ring)]',
                ! hasOptions ? 'border-dashed border-amber-300 bg-amber-50/40' : '',
            ]"
        >
            <span
                class="truncate"
                :class="(! hasOptions || value === null || value === undefined || value === '') ? 'text-slate-400' : 'text-slate-900'"
                x-text="selectedLabel"
            ></span>
            <span class="flex shrink-0 items-center gap-1 text-slate-400">
                <span
                    x-show="! hasOptions"
                    x-cloak
                    class="rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700"
                >Boş</span>
                <svg class="h-4 w-4 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </span>
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
                    style="--ss-accent: {{ $accent }}; --ss-accent-soft: color-mix(in srgb, {{ $accent }} 15%, white); --ss-ring: color-mix(in srgb, {{ $accent }} 20%, transparent);"
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

    <p x-show="! hasOptions" x-cloak class="text-xs text-amber-700">{{ $emptyOptionsLabel }}. Önce ilgili kaydı oluşturun veya filtreyi değiştirin.</p>
</div>
