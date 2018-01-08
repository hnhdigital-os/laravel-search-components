<?php

namespace HnhDigital\SearchComponents\Composers;

use Illuminate\Contracts\View\View;

class Search
{
    /**
     * Composing search results.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view_data = $view->getData();

        $view->with('form_id', array_get($view_data, 'form_id', ''))
            ->with('table_class', array_get($view_data, 'table_class'));
    }

}
