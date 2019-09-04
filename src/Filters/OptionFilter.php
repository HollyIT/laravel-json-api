<?php

namespace Hollyit\LaravelJsonApi\Filters;

use Illuminate\Validation\Rule;
use Hollyit\LaravelJsonApi\QueryValidator;

class OptionFilter extends BaseFilter
{
    /** @var bool */
    protected $hasAll = false;

    /** @var array */
    protected $options = [];

    /**
     * @param $all
     * @return $this
     */
    public function hasAll($all)
    {
        $this->hasAll = $all;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param  array  $options
     * @return OptionFilter
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function rules(QueryValidator $validator)
    {
        $options = $this->options;
        if ($this->hasAll) {
            $options[] = 'all';
        }

        if ($this->multiple) {
            $validator->addFilterRule($this->key, ['sometimes', 'array']);
            $validator->addFilterRule($this->key.'.*', ['sometimes', Rule::in($options)]);
        } else {
            $validator->addFilterRule($this->key, ['sometimes', Rule::in($options)]);
        }
    }
}
