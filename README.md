# Laravel Livewire Search Select

Aramalı select (combobox) — Laravel Livewire + Alpine.

## Kurulum

```bash
composer require enesekinci/laravel-livewire-search-select
```

Private GitHub ise:

```bash
composer require enesekinci/laravel-livewire-search-select:^1.0
```

`composer.json` repositories:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/enesekinci/laravel-livewire-search-select"
    }
  ]
}
```

Gereksinimler: Livewire 3/4 (Alpine dahil), Tailwind önerilir.

## Kullanım

```blade
<x-search-select
    wire:model.live="customer_id"
    label="Müşteri"
    placeholder="Ara..."
    empty-label="— Seçilmedi —"
    :options="[
        ['value' => 1, 'label' => 'Ada Ltd'],
        ['value' => 2, 'label' => 'Beta A.Ş.'],
    ]"
/>
```

Zorunlu alan (boş seçenek yok):

```blade
<x-search-select
    wire:model="unit"
    :nullable="false"
    :options="[['value' => 'AD', 'label' => 'AD']]"
/>
```

Renk (opsiyonel):

```blade
<x-search-select accent="#0b5cab" ... />
```

Alias: `<x-admin.search-select />` aynı componenti gösterir.

## View override

```bash
php artisan vendor:publish --tag=search-select-views
```

## Lisans

MIT
