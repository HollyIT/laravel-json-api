<?php

namespace Hollyit\LaravelJsonApi\Controller;

use Auth;
use Hollyit\LaravelJsonApi\CollectionRequest;
use Illuminate\Routing\Controller as BaseController;

class JsonApiController extends BaseController
{
    public function index(CollectionRequest $request)
    {
           return $request->result();
    }
}
