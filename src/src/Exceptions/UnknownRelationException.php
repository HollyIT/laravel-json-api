<?php

namespace Hollyit\LaravelJsonApi\Exceptions;

use Exception;

class UnknownRelationException extends Exception
{
    private $include;

    public function __construct($include)
    {
        $this->include = $include;
        parent::__construct('Unknown include '.$include, 0, null);
    }

    /**
     * @return mixed
     */
    public function getInclude()
    {
        return $this->include;
    }
}
