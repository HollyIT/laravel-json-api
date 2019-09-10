<?php

namespace Hollyit\LaravelJsonApi;

use Illuminate\Support\Facades\Route;
use Hollyit\LaravelJsonApi\Middleware\JsonResponseMiddleware;

class JsonApi
{
    /**
     * @var \Hollyit\LaravelJsonApi\Schema[]
     */
    protected $instances = [];

    /**
     * @param $name
     * @param $ident
     * @param $instanceClass
     */
    public static function resourceRoute($name, $ident, $instanceClass, $closure=null)
    {
        $controller = config('jsonapi.default_controller');
        $indexRoute = Route::get($name, [
            'as'                => $name.'.index',
            'uses'              => $controller.'@index',
            'json_api_resource' => $instanceClass,
            'json_api_method'   => 'index',
        ])
            ->middleware(JsonResponseMiddleware::class);

        $viewRoute = Route::get($name.'/{'.$ident.'}', [
            'as'                => $name.'.show',
            'uses'              => $controller.'@index',
            'json_api_resource' => $instanceClass,
            'json_api_method'   => 'show',
            'json_api_ident'    => $ident,
        ])
            ->middleware(JsonResponseMiddleware::class);

        if (is_callable($closure)) {
            $closure($indexRoute, $viewRoute);
        }
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
