<?php

namespace Hollyit\LaravelJsonApi;

use Illuminate\Support\Facades\Facade;

/**
 * Class JsonApiFacade
 *
 * @package Hollyit\LaravelJsonApi
 *
 * @see \Hollyit\LaravelJsonApi\JsonApi
 */
class JsonApiFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return JsonApi::class;
    }
}
