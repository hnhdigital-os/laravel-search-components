<?php

namespace HnhDigital\SearchComponents;

use Html;
use Illuminate\Pagination\Paginator;
use HnhDigital\ModelSearch\ModelSearch;
use Request;
use Tag;

class Search
{

    /**
     * Config.
     *
     * @var array
     */
    private $config;

    /**
     * Construct.
     */
    public function __construct($config = [])
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the table header.
     *
     * @return mixed
     */
    private function getHeader()
    {
        return '';
    }

    /**
     * Get Form ID.
     *
     * @return string
     */
    private function getFormId()
    {
        return 'hnhdigital-'.array_get($this->config, 'name', '').'-form';
    }

    /**
     * Get the colgroup.
     *
     * @return string
     */
    private function getColgroup()
    {
        $total_columns = $this->checkColumns();
        $colgroup = array_get($this->config, 'colgroup', []);

        $html = '';
        for ($column = 0; $column < $total_columns; $column++) {
            $html .= Html::col()->width(array_get($colgroup, $column.'.width', ''));
        }

        if ($total_columns > 0) {
            $html = Html::colgroup($html);
        }

        return $html;
    }

    /**
     * Check total columns.
     *
     * @return integer
     */
    private function checkColumns()
    {
        $total_columns = array_get($this->config, 'colgroup.columns', 1);

        return $total_columns;
    }

    /**
     * Get search input.
     *
     * @return string
     */
    private function getSearchInput()
    {
        $total_columns = $this->checkColumns();
        $search_input = array_get($this->config, 'search_input', []);
        $tbody = Tag::tbody();

        // No search input.
        if (empty($search_input)) {
            return $tbody;
        }

        $tbody = Tag::tbody();
        $tr = $tbody->tr(['class' => 'search-input']);
        $td_html = '';

        // Default.
        if ($search_input === true) {
            $td_html = Html::input()->name('lookup')->addClass('search-field form-control')->form($this->form_id)->s();
        } else {

        }

        $tr->td(
            ['colspan' => $total_columns],
            $td_html
        );

        return $tbody;
    }

    /**
     * Get search header.
     *
     * @return string
     */
    private function getSearchHeader()
    {
        $total_columns = $this->checkColumns();
        $search_header = array_get($this->config, 'search_header', []);
        $tbody = Tag::tbody();

        // No search header.
        if (empty($search_header)) {
            return $tbody;
        }

        $tr = $tbody->tr(['class' => 'search-input']);
        $td_html = 'test';

        $tr->td(
            ['colspan' => $total_columns],
            $td_html
        );

        return $tbody;
    }

    /**
     * Get search info.
     *
     * @return string
     */
    private function getSearchInfo()
    {
        $total_columns = $this->checkColumns();
        $search_info = array_get($this->config, 'search_info', []);
        $tbody = Tag::tbody();

        // No search header.
        if (empty($search_info)) {
            return $tbody;
        }

        $request = $this->parseRequest();

        if (count($request) == 0) {
            return $tbody;
        }

        $td_html = 'Filtering by: '.implode('; ', $request).'. ';

        $tr = $tbody->tr(['class' => 'search-info']);
        $tr->td(
            ['colspan' => $total_columns],
            $td_html
        );

        return $tbody;
    }

    /**
     * Parse the request.
     *
     * @return array
     */
    private function parseRequest()
    {
        $result = [];

        // Check the ModelSearch trait method.
        if (method_exists($this->config['model'], 'getSearchableAttributes')) {
            $attributes = $this->config['model']->getSearchableAttributes();

            foreach ($this->request as $key => $value) {
                if (!array_has($attributes, $key)) {
                    continue;
                }

                list($operator_name, $operator, $value) = ModelSearch::parseInlineOperator($value);

                $title = array_get($attributes, $key.'.title', $key);

                $result[] = sprintf('<strong>%s</strong> %s <strong>%s</strong>', $title, $operator_name, $value);
            }
        }

        return $result;
    }

    /**
     * Get search footer.
     *
     * @return string
     */
    private function getSearchFooter()
    {
        $total_columns = $this->checkColumns();
        $tbody = Tag::tbody();

        return $tbody;
    }

    /**
     * Get the empty search result.
     *
     * @return mixed
     */
    private function getSearchEmpty()
    {
        $search_empty = array_get($this->config, 'search_empty', []);
        $tbody = Tag::tbody();

        if (empty($search_empty)) {
            return $tbody;
        }

        $total_columns = $this->checkColumns();
        $tr = $tbody->tr();

        if ($search_empty === true) {
            $row_html = 'No <strong>records</strong> found.';
        }

        $tr->td(
            ['colspan' => $total_columns, 'style' => 'line-height: 50px;text-align:center;'],
            Html::span($row_html)->s()
        );

        return $tbody;
    }

    /**
     * Get the result.
     *
     * @return mixed
     */
    private function getResult()
    {
        if (array_get($this->config, 'paginator.total', 0) === 0) {
            return $this->search_empty;
        }

        $info = $this->search_info->prepare(['ignore_tags' => 'tbody']);
        $results = $this->config['result']->prepare(['ignore_tags' => 'tbody']);

        if (request::ajax()) {
            return array_merge($info, $results);
        }

        return $info.$results;
    }

    /**
     * Get default pagination data.
     *
     * @return array
     */
    private function getPaginationPerPage()
    {
        $pagination_per_page = array_get($this->config, 'pagination_per_page', 15);
        $page = array_get($this->config, 'page', 1);
        $page = request('page', $page);
        $this->config['page'] = $page;

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        return $pagination_per_page;
    }

    /**
     * Get pagination data.
     *
     * @return array
     */
    private function getPaginator($item = null)
    {
        if (!is_null($item)) {
            return array_get($this->config, 'paginator.'.$item, '');
        }

        return array_get($this->config, 'paginator', []);
    }

    /**
     * Get pagination data.
     *
     * @return array
     */
    private function getPagination($item = null)
    {
        return $this->pagination_per_page;
    }

    /**
     * Get the result.
     *
     * @return void
     */
    private function setResult($html)
    {
        $this->config['result'] = $html;
    }

    /**
     * Request.
     *
     * @param string|array $name
     *
     * @return void
     */
    private function setRequest($name)
    {
        $this->config['request'] = is_array($name) ? $name : request($name, []);
    }

    /**
     * Set the name of this search.
     *
     * @return void
     */
    private function setName($name)
    {
        $this->config['name'] = $name;
        $this->form_id = $name;
    }

    /**
     * Set the search model class of this search.
     *
     * @return void
     */
    private function setClass($class)
    {
        if (class_exists($class)) {
            $this->config['class'] = $class;
            $this->config['model'] = new $class;
        }
    }

    /**
     * Set the name of this search.
     *
     * @return void
     */
    private function setQuery($query)
    {
        $this->config['query'] = $query;
    }

    /**
     * Set the source route.
     *
     * @return void
     */
    private function setRoute(...$arguments)
    {
        $this->config['route'] = route(...$arguments);
    }

    /**
     * Set the source route.
     *
     * @return void
     */
    private function setFallbackRoute(...$arguments)
    {
        $this->config['fallback_route'] = route(...$arguments);
    }

    /**
     * Results from the paginator.
     *
     * @param Paginator $results
     *
     * @return void
     */
    private function setPaginator($results)
    {
        $paginator = array_get($this->config, 'paginator', []);

        $paginator['count'] = $results->count();
        $paginator['page'] = $results->currentPage();
        $paginator['first_item'] = $results->firstItem();
        $paginator['has_more_pages'] = $results->hasMorePages();
        $paginator['last_item'] = $results->lastItem();
        $paginator['last_page'] = $results->lastPage();
        $paginator['next_page_url'] = $results->nextPageUrl();
        $paginator['per_page'] = $results->perPage();
        $paginator['previous_page_url'] = $results->previousPageUrl();
        $paginator['total'] = $results->total();

        $this->config['paginator'] = $paginator;
    }

    /**
     * Run the query.
     *
     * @return Collection
     */
    public function run($query)
    {
        $this->query = clone $query;

        // Run query.
        $results = $query->paginate($this->pagination);
        $this->paginator = $results;
        return $results;
    }

    /**
     * Get a static value.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->{camel_case($name)}();
    }

    /**
     * Get a static value.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->{camel_case($name)}($value);
    }

    /**
     * Call a method.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        preg_match('/^(get|set)(.*)$/', $method, $matches);

        if (count($matches)) {
            if (method_exists($this, $matches[0])) {
                return $this->{$matches[0]}(...$arguments);
            }

            return $matches[1] == 'get' ? '' : $this;
        }

        if (count($arguments) == 0) {
            $get_method = 'get'.studly_case($method);

            if (method_exists($this, $get_method)) {
                return $this->{$get_method}();
            }

            return array_get($this->config, snake_case($method), '');
        }

        $set_method = 'set'.studly_case($method);

        if (method_exists($this, $set_method)) {
            $this->{$set_method}(...$arguments);

            return $this;
        }

        if (count($arguments) == 1) {
            $this->config[snake_case($method)] = array_get($arguments, 0);
        }

        return $this;
    }

    /**
     * Render the results.
     *
     * @return mixed
     */
    public function render($html)
    {
        $this->result = $html;

        if (request::ajax()) {
            return [
                'header' => $this->search_header,
                'rows'   => $this->result,
                'footer' => $this->search_footer,
                'total'  => array_get($this->config, 'paginator.total'),
            ];
        }

        return $this;
    }
}
