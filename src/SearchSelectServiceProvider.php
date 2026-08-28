<?php

namespace EnesEkinci\SearchSelect;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SearchSelectServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'search-select');

        Blade::component('search-select::components.search-select', 'search-select');

        // Optional alias used in some admin panels
        Blade::component('search-select::components.search-select', 'admin.search-select');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/search-select'),
            ], 'search-select-views');
        }
    }
}
