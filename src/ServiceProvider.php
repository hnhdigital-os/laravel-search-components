<?php

namespace HnhDigital\SearchComponents;

use View;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
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
