<?php

namespace HnhDigital\SearchComponents;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use View;

class SearchProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'hnhdigital-search-component');

        view()->composer('hnhdigital-search-component::search', Composers\Search::class);
    }
}
