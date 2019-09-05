<?php

namespace Hollyit\LaravelJsonApi;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Responsable;

class ResourceResponse implements Responsable
{
    /**
     * @var \Hollyit\LaravelJsonApi\JsonApi
     */
    public $api;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $includes;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $resource;

    /** @var Request */
    protected $request;

    /** @var \Hollyit\LaravelJsonApi\Schema */
    protected $schema;

    /** @var array */
    protected $data;

    /** @var array */
    protected $relations = [];

    /** @var array */
    protected $meta = [];

    /** @var \Hollyit\LaravelJsonApi\QueryValidator */
    protected $validator;

    /**
     * ResourceResponse constructor.
     *
     * @param $resource
     * @param  \Hollyit\LaravelJsonApi\JsonApi  $api
     */
    public function __construct($resource, JsonApi $api)
    {
        $this->resource = $resource;
        $this->api = $api;
        $this->schema = $api->getSchema($resource);

        $this->includes = collect([]);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setMeta($key, $value)
    {
        Arr::set($this->meta, $key, $value);

        return $this;
    }

    /**
     * @param $request
     * @return $this
     */
    public function withRequest($request)
    {
        if ($request instanceof Request) {
            if ($request->has('include')) {
                $this->withIncludes(array_filter(array_map('trim', explode(',', $request->get('include')))));
            }
        }
        $this->request = $request;

        return $this;
    }

    /**
     * @param $includes
     * @return $this
     */
    public function withIncludes($includes)
    {
        $includes = is_array($includes) ? $includes : [];
        $this->includes = collect($includes);

        return $this;
    }

    /**
     * @param $name
     * @param $id
     * @param $data
     * @return $this
     */
    public function addRelation($name, $id, $data)
    {
        Arr::set($this->relations, $name.'.'.$id, $data);

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return response()->json($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (! $this->data) {
            $this->render();
        }

        return $this->data;
    }

    /**
     *
     */
    protected function render()
    {
        $this->data = [];
        if ($this->includes->isNotEmpty()) {
            $this->loadIncludes();
        }
        $this->data['data'] = $this->schema->toResource($this->resource, $this->includes, $this);
        $this->data['includes'] = [];
        foreach ($this->relations as $domain) {
            foreach ($domain as $item) {
                $this->data['includes'][] = $item;
            }
        }
        $this->data['meta'] = $this->meta;
    }

    /**
     *
     */
    protected function loadIncludes()
    {
        $this->validateIncludes();

        $toLoad = [];
        foreach ($this->includes as $include) {
            if (! $this->resource->relationLoaded($include)) {
                $toLoad[] = $include;
            }
        }
        $this->resource->load($toLoad);
    }

    public function validateIncludes()
    {
        $includes = [];
        foreach ($this->includes as $include) {
            Arr::set($includes, $include, true);
        }

        $this->schema->validateIncludes($includes);
    }

    /**
     * @return \Hollyit\LaravelJsonApi\QueryValidator
     */
    public function validate()
    {
        if (! $this->validator) {
            $this->validator = $this->schema->validate([
                'includes' => $this->includes,
            ]);
        }

        return $this->validator;
    }
}
