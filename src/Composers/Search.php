<?php

namespace HnhDigital\SearchComponents\Composers;

use Illuminate\Contracts\View\View;
use HnhDigital\SearchComponents\Search as SearchClass;

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

        if (empty($data['data'])) {
            $data['data'] = new SearchClass();
        }

        $view->with('search', $data['data']);
    }
}
