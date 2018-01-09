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
        $data = $view->getData();

        $view->with('search', $data['data']);
    }
}
