<?php

namespace Hollyit\LaravelJsonApi;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class CollectionRequest
 *
 * @package Hollyit\LaravelJsonApi
 *
 * @property \Illuminate\Routing\Route $route;
 */
class CollectionRequest extends Request
{
    /**
     * @return \Hollyit\LaravelJsonApi\ResourceCollectionResponse|\Hollyit\LaravelJsonApi\ResourceResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function result()
    {

        /** @var \Illuminate\Routing\Route $route */
        $route = call_user_func($this->getRouteResolver());

        $method = $route->getAction('json_api_method');
        /** @var \Hollyit\LaravelJsonApi\JsonApi $jsonApi */
        $jsonApi = app(JsonApi::class);
        /** @var \Hollyit\LaravelJsonApi\ResourceResponse $resource */
        $resource = null;

        switch ($method) {
            case 'index':
                $class = $route->getAction('json_api_resource');
                if (! $class) {
                    abort(404);
                }

                $resource = $jsonApi->collection($class);

                break;

            case 'show':
                $intent = $route->parameter($route->getAction('json_api_ident'));

                if (! $intent) {
                    abort(404);
                }
                $resource = $jsonApi->resource($intent);
                break;

            default:
                abort(404);
        }
        $resource->withRequest($this);
        if (! $resource->validate()
            ->passes()) {
            throw new ValidationException($resource->validate());
        }
        return $resource;
    }
}
