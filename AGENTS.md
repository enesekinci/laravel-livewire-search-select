# AGENTS.md — laravel-livewire-search-select

## Purpose
Searchable select for Livewire (`x-search-select` / `x-admin.search-select`).

## Hard rules for AI agents
1. For foreign keys / long option lists (customers, products, categories, units), use this component — not native `<select>` with 50+ options.
2. Usage:
   ```blade
   <x-search-select wire:model.live="customer_id" :options="$customers" label="Müşteri" />
   ```
3. Options format: `[['value' => 1, 'label' => 'Acme'], ...]` (follow existing package API in the consuming app).
4. Do not ask the user if searchable select is needed — if the list can grow, use it.
5. Tailwind `@source` must include this package views.
