<?php

namespace Hollyit\LaravelJsonApi;

use Hollyit\LaravelJsonApi\Routing\RouteRegistrar;

class JsonApi
{
    /**
     * @var \Hollyit\LaravelJsonApi\Schema[]
     */
    protected $instances = [];

    /**
     * @param $instanceClass
     * @param  null  $closure
     * @return \Hollyit\LaravelJsonApi\Routing\RouteRegistrar
     */
    public function resourceRoute($instanceClass, $closure = null)
    {

        $registrar = new RouteRegistrar($this->getSchema($instanceClass));
        if (is_callable($closure)) {
            call_user_func($closure, $registrar);
        }
        return $registrar;
    }

    /**
     * @param $class
     * @param  int  $version
     * @return \Hollyit\LaravelJsonApi\Schema
     */
    public function getSchema($class, $version = 1)
    {
        $className = is_string($class) ? $class : get_class($class);
        if (! isset($this->instances[$className.$version])) {
            $name = forward_static_call([$className, 'getApiSchema'], $version);
            $this->instances[$className.$version] = new $name($this);
        }

        return $this->instances[$className.$version];
    }

    /**
     * @param $resource
     * @return \Hollyit\LaravelJsonApi\ResourceResponse
     */
    public function resource($resource)
    {
        return new ResourceResponse($resource, $this);
    }

    /**
     * @param $className
     * @return \Hollyit\LaravelJsonApi\ResourceCollectionResponse
     */
    public function collection($className)
    {
        return new ResourceCollectionResponse($className, $this);
    }
}
