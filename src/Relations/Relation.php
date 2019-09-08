<?php

namespace Hollyit\LaravelJsonApi\Relations;

use Hollyit\LaravelJsonApi\Schema;
use Hollyit\LaravelJsonApi\ResourceResponse;

abstract class Relation
{
    /** @var string */
    public $name;

    /** @var string */
    public $relationName;


    /** @var string */
    protected $resourceClass;

    /**
     * @var \Hollyit\LaravelJsonApi\Schema
     */
    public $schema;

    /**
     * Relation constructor.
     *
     * @param $resourceClass
     * @param $name
     * @param $relationName
     * @param  \Hollyit\LaravelJsonApi\Schema  $schema
     */
    public function __construct($resourceClass, $name, $relationName, Schema $schema)
    {
        $this->name = $name;
        $this->relationName = $relationName;
        $this->resourceClass = $resourceClass;
        $this->schema = $schema;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRelationName(): string
    {
        return $this->relationName;
    }
}
