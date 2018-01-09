<?php

namespace HnhDigital\SearchComponents;

use HnhDigital\ModelSearch\ModelSearch;
use Html;
use Illuminate\Pagination\Paginator;
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
     * Check total columns.
     *
     * @return int
     */
    private function checkColumns()
    {
        $total_columns = array_get($this->config, 'columns.total', 1);

        return $total_columns;
    }

    /**
     * Parse the request.
     *
     * @return array
     */
    private function parseRequest()
    {
        $result = [
            'count'      => 0,
            'attributes' => [],
            'text'       => [],
        ];

        $count = 0;

        // Check the ModelSearch trait method.
        if (method_exists($this->config['model'], 'getSearchableAttributes')) {
            $result['attributes'] = $this->config['model']->getSearchableAttributes();

            foreach ($this->request as $key => $value) {
                if (!array_has($result['attributes'], $key) || empty($value)) {
                    continue;
                }

                list($operator_name, $operator, $value) = ModelSearch::parseInlineOperator($value);
                $title = array_get($result['attributes'], $key.'.title', $key);
                $result['text'][] = sprintf('<strong>%s</strong> %s <strong>%s</strong>', $title, $operator_name, $value);

                $count++;
            }
        }

        $result['count'] = $count;

        array_set($this->config, 'parsed_request', $result);

        return $result;
    }

    /**
     * Get the columns.
     *
     * @return string
     */
    private function getColumns()
    {
        $total_columns = $this->checkColumns();
        $columns = array_get($this->config, 'columns', []);

        $html = '';
        for ($column = 0; $column < $total_columns; $column++) {
            $html .= Html::col()->width(array_get($columns, $column.'.width', ''));
        }

        if ($total_columns > 0) {
            $html = Html::colgroup($html);
        }

        return $html;
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
    private function getPagination($item = null)
    {
        return $this->pagination_per_page;
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

        $this->parseRequest();

        if (array_get($this->config, 'parsed_request.count', 0) == 0) {
            return $tbody;
        }

        $td_html = 'Filtering by: '.implode('; ', array_get($this->config, 'parsed_request.text', [])).'. ';

        $tr = $tbody->tr(['class' => 'search-info']);
        $tr->td(
            ['colspan' => $total_columns],
            $td_html
        );

        return $tbody;
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
            $td_html = Html::input()->name('lookup')->value(array_get($this->config, 'request.lookup', ''))->addClass('search-field form-control')->form($this->form_id)->s();
        } else {
        }

        $tr->td(
            ['colspan' => $total_columns],
            $td_html
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
        $info = $this->search_info->prepare(['ignore_tags' => 'tbody']);

        if (array_get($this->config, 'paginator.total', 0) === 0) {
            $empty = $this->search_empty->prepare(['ignore_tags' => 'tbody']);

            return request::ajax() ? array_merge($info, $empty) : $info.$empty;
        }

        $results = $this->config['result']->prepare(['ignore_tags' => 'tbody']);

        return request::ajax() ? array_merge($info, $results) : $info.$results;
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
        $row_html = '';

        $row_html = 'No <strong>'.array_get($search_empty, 'name').'</strong> found.';

        $tr->td(
            ['colspan' => $total_columns, 'style' => 'line-height: 50px;text-align:center;'],
            Html::span($row_html)->s()
        );

        return $tbody;
    }

    /**
     * Set the search empty.
     *
     * @return string
     */
    private function setSearchEmpty($config = false)
    {
        // Turn off the the search empty option.
        if ($config === false) {
            array_set($this->config, 'search_empty', false);

            return;
        }

        if ($config === true) {
            $config = [];
        }

        // Default config.
        $default_config = [
            'name' => 'records',
        ];

        $config = array_replace_recursive($default_config, $config);

        array_set($this->config, 'search_empty', $config);
    }

    /**
     * Get session.
     *
     * @return string
     */
    private function getSession()
    {
        return session($this->session_name, []);
    }

    /**
     * Get the unique session name.
     *
     * @return string
     */
    private function getSessionName()
    {
        // Use the unique route as the session name.
        if (array_has($this->config, 'route_text')) {
            $name = str_replace(['[', ']', '::', ' '], ['', '-', '-', ''], array_get($this->config, 'route_text', ''));
            $name .= '-'.hash('sha256', serialize(array_get($this->config, 'route_parameters', '')));

            return $name;
        }

        $name = $this->name;

        // Use the provided class and search name.
        if (array_has($this->config, 'class')) {
            if (array_get($this->config, 'class', false)) {
                $name .= '_'.snake_case(str_replace('\\', '', $this->config['class']));
            }

            return $name;
        }

        return $name;
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
        // Get the data from the request or provided array.
        $request = is_array($name) ? $name : request($name, []);

        // Get the session.
        if (empty($request)) {
            $request = $this->session;
        }

        // Set the session, and the request data.
        $this->session = $this->config['request'] = $request;
    }

    /**
     * Set session.
     *
     * @return void
     */
    private function setSession($data)
    {
        session([$this->session_name => $data]);
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
            $this->config['model'] = new $class();
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
        $this->config['route_text'] = array_get($arguments, 0, '');
        $this->config['route_parameters'] = array_get($arguments, 1, []);
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

        array_set($this->config, 'paginator', $paginator);
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
        if (array_has($this->config, 'query_all', false)) {
            $results = $query->get();
            array_set($this->config, 'paginator.count', $results->count());
            array_set($this->config, 'paginator.total', $results->count());

            return $results;
        }

        $results = $query->paginate($this->pagination);
        $this->paginator = $results;

        return $results;
    }

    /**
     * Get a static value.
     *
     * @param string $method
     * @param array  $arguments
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
     * @param array  $arguments
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
     * @param array  $arguments
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
