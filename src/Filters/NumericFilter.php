<?php

namespace Hollyit\LaravelJsonApi\Filters;

use Hollyit\LaravelJsonApi\QueryValidator;

class NumericFilter extends BaseFilter
{
    /** @var null |integer */
    protected $min = null;

    /** @var null | integer */
    protected $max = null;

    /**
     * @return null
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param  null |integer  $min
     * @return NumericFilter
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return null
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param  null |integer  $max
     * @return NumericFilter
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @param  \Hollyit\LaravelJsonApi\QueryValidator  $validator
     */
    public function rules(QueryValidator $validator)
    {
        $rules = ['sometimes', 'numeric'];
        if (! is_null($this->min)) {
            $rules[] = 'min:'.$this->min;
        }

        if (! is_null($this->max)) {
            $rules[] = 'max:'.$this->max;
        }

        if ($this->isMultiple()) {
            $validator->addFilterRule($this->getKey(), ['sometimes', 'array']);
            $validator->addFilterRule($this->getKey().'.*', $rules);
        } else {
            $validator->addFilterRule($this->getKey(), $rules);
        }
    }
}
