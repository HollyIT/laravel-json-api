<?php

namespace Hollyit\LaravelJsonApi\Filters;

use Hollyit\LaravelJsonApi\QueryValidator;

class BooleanFilter extends BaseFilter
{
    /**
     * @param  \Hollyit\LaravelJsonApi\QueryValidator  $validator
     */
    public function rules(QueryValidator $validator)
    {
        $validator->addFilterRule($this->getKey(), ['sometimes', 'boolean']);
    }
}
