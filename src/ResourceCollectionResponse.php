<?php

namespace Hollyit\LaravelJsonApi;

use Illuminate\Http\Request;

class ResourceCollectionResponse extends ResourceResponse
{
    /**
     * @var \Illuminate\Support\Collection
     */
    public $filters;

    /**
     * @var string
     */
    protected $sort = '';

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $perPage = 15;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /** @var \Hollyit\LaravelJsonApi\QueryValidator */
    protected $validator;

    public function __construct($resource, JsonApi $api)
    {
        $resource = new $resource();
        parent::__construct($resource, $api);
        $this->filters = collect([]);
    }

    /**
     * @param $request
     * @return \Hollyit\LaravelJsonApi\ResourceResponse
     */
    public function withRequest($request)
    {
        if ($request instanceof Request) {
            if ($request->has('filter') && is_array($request->get('filter'))) {
                $this->withFilters($request->get('filter'));
            }
            if ($request->has('sort')) {
                $this->withSort($request->get('sort'));
            }

            if ($request->has('page')) {
                $this->page = $request->get('page');
            }
        }

        return parent::withRequest($request);
    }

    /**
     * @param $filters
     * @return $this
     */
    public function withFilters($filters)
    {
        $filters = is_array($filters) ? $filters : [];
        $this->filters = collect($filters);

        return $this;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function withSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param  integer  $page
     * @return ResourceCollectionResponse
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     *
     */
    protected function render()
    {
        $this->data = [];
        if (! $this->validator) {
            $this->validate();
        }
        $builder = $this->schema->createBuilder($this);
        $collection = $builder->paginate($this->perPage);
        $this->data['data'] = [];
        foreach ($collection as $resource) {
            $this->data['data'][] = $this->schema->toResource($resource, $this->includes, $this);
        }

        $this->data['includes'] = [];
        foreach ($this->relations as $domain) {
            foreach ($domain as $item) {
                $this->data['includes'][] = $item;
            }
        }

        $this->data['pager'] = [
            'total'        => $collection->total(),
            'last_page'    => $collection->lastPage(),
            'per_page'     => $collection->perPage(),
            'current_page' => $collection->currentPage(),
        ];

        $this->data['meta'] = $this->meta;
    }

    /**
     * @return \Hollyit\LaravelJsonApi\QueryValidator
     */
    public function validate()
    {
        if (! $this->validator) {

            $this->validator = $this->schema->validate([
                'filters'  => $this->filters,
                'includes' => $this->includes,
                'sort'     => $this->sort,
            ]);
        }

        return $this->validator;
    }
}
