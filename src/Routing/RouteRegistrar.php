<?php

namespace Hollyit\LaravelJsonApi\Routing;

use Hollyit\LaravelJsonApi\Schema;
use Illuminate\Support\Facades\Route;

class RouteRegistrar
{
    /**
     * @var string
     */
    protected static $defaultController;

    /***
     * @var \Illuminate\Routing\Route
     */
    public $indexRoute;

    /**
     * @var \Illuminate\Routing\Route
     */
    public $viewRoute;

    /**
     * @var \Hollyit\LaravelJsonApi\Schema
     */
    protected $schema;

    /**
     * @var string
     */
    protected $routePrefix;

    /**
     * RouteRegistrar constructor.
     *
     * @param  \Hollyit\LaravelJsonApi\Schema  $schema
     */
    public function __construct(Schema $schema)
    {
        if (! static::$defaultController) {
            static::$defaultController = config('jsonapi.default_controller');;
        }
        $this->schema = $schema;
        $this->routePrefix = $this->schema->routePrefix();
    }

    /**
     * @param $prefix
     * @return $this
     */
    public function routePrefix($prefix)
    {
        $this->routePrefix = $prefix;

        return $this;
    }

    /**
     * @param  string | null  $uri
     * @param  string | null  $controller
     * @param  closure | null  $closure
     * @return \Illuminate\Routing\Route
     */
    public function index($uri = null, $controller = null, $closure = null)
    {
        $uri = $uri ?: $this->routePrefix;
        $route = $this->registerRoute($uri, 'index', 'get', $controller, [], $closure);
        $this->indexRoute = $route;

        return $route;
    }

    /**
     * @param $uri
     * @param $method
     * @param  string | null  $routeMethod
     * @param  string | null  $controller
     * @param  array | null  $options
     * @param  \Closure | null  $closure
     * @return \Illuminate\Routing\Route
     */
    public function registerRoute(
        $uri,
        $method,
        $routeMethod = null,
        $controller = null,
        $options = [],
        $closure = null
    ) {
        if (! $routeMethod) {
            if ($method === 'show' || $method === 'list') {
                $routeMethod = 'get';
            } else {
                $routeMethod = $method;
            }
        }
        $options = array_merge($options, [
            'as'                => $this->routePrefix.'.'.$method,
            'uses'              => $controller ?: static::$defaultController,
            'json_api_resource' => get_class($this->schema->getInstance()),
            'json_api_method'   => $method,
            'json_api_ident'    => $this->schema->routeIdent(),
        ]);

        $route = forward_static_call([Route::class, $routeMethod], $uri, $options);
        if (is_callable($closure)) {
            call_user_func($closure, $route, $this);
        }

        return $route;
    }

    /**
     * @param  string | null  $uri
     * @param  string | null  $controller
     * @param  closure | null  $closure
     * @return \Illuminate\Routing\Route
     */
    public function view($uri = null, $controller = null, $closure = null)
    {
        $uri = $uri ?: $this->resourceRoute();
        $route = $this->registerRoute($uri, 'view', 'get', $controller, [], $closure);

        $this->viewRoute = $route;

        return $route;
    }

    public function resourceRoute()
    {
        return $this->routePrefix.'/{'.$this->schema->routeIdent().'}';
    }
}
