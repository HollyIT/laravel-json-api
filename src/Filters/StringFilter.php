<?php

namespace Hollyit\LaravelJsonApi\Filters;

use Hollyit\LaravelJsonApi\QueryValidator;

class StringFilter extends BaseFilter
{
    public function rules(QueryValidator $validator)
    {
        $validator->addFilterRule($this->getKey(), ['sometimes', 'string']);
    }
}
