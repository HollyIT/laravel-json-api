<?php

namespace Hollyit\LaravelJsonApi\Relations;

use Illuminate\Database\Eloquent\Builder;
use Hollyit\LaravelJsonApi\ResourceResponse;

abstract class Relation
{
    /** @var string */
    public $name;

    /** @var string */
    public $relationName;

    /** @var null|\Closure */
    protected $queryCallback;

    /** @var string */
    protected $resourceClass;

    /**
     * Relation constructor.
     *
     * @param $resourceClass
     * @param $name
     * @param $relationName
     */
    public function __construct($resourceClass, $name, $relationName)
    {
        $this->name = $name;
        $this->relationName = $relationName;
        $this->resourceClass = $resourceClass;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function queryCallback($callback)
    {
        $this->queryCallback = $callback;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @param $includes
     * @param  \Hollyit\LaravelJsonApi\ResourceResponse  $response
     * @return mixed
     */
    abstract public function toArray($resource, $includes, ResourceResponse $response);

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     */
    public function addInclude(Builder $builder)
    {
        $builder->with($this->relationName);
    }
}
