<?php

namespace Hollyit\LaravelJsonApi;

use Illuminate\Support\Arr;

class IncludesBuilder
{
    /**
     * @var \Hollyit\LaravelJsonApi\Schema
     */
    protected $schema;

    /**
     * @var array
     */
    protected $includes;

    protected $results = null;

    /**
     * IncludesBuilder constructor.
     *
     * @param  \Hollyit\LaravelJsonApi\Schema  $schema
     * @param  array |\Illuminate\Support\Collection  $includes
     */
    public function __construct(Schema $schema, $includes)
    {
        $this->schema = $schema;
        $this->includes = $includes;
    }

    public function applyTo($builder)
    {
        $includes = $this->buildIncludes();
        $builder->with($includes);
    }

    public function buildIncludes()
    {
        if ($this->results) {
            return $this->results;
        }

        $this->results = [];
        $includesTree = [];
        foreach ($this->includes as $include) {
            Arr::set($includesTree, $include, 0);
        }
        $this->walk($includesTree, $this->schema);

        return $this->results;
    }

    protected function walk($includes, Schema $schema, $prefix = '')
    {
        foreach ($includes as $key => $children) {
            $relation = $schema->getRelation($key);
            $name = $relation->getRelationName();
            if ($relation) {
                $query = $relation->getQuery($this->schema);
                if ($query) {
                    $this->results[$prefix.$name] = $query;
                } else {
                    $this->results[] = $prefix.$name;
                }
                if ($children) {

                    $this->walk($children, $relation->relationSchema(), $prefix.$name.'.');
                }
            }
        }
    }
}
