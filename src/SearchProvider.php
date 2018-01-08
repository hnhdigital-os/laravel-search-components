<?php

namespace HnhDigital\SearchComponents;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use View;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/Views/', 'hnhdigital-search-components');
        
        view()->composer('hnhdigital-search-components::search', 'HnhDigital\SearchComponents\Composers\Search');
    }
}
