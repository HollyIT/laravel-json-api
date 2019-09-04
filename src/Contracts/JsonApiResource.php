<?php

namespace Hollyit\LaravelJsonApi\Contracts;

interface JsonApiResource

{
    /**
     * @param $version
     * @return string the class name of the resources schema
     */
    public static function getApiSchema($version);
}
