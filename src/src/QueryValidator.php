<?php

namespace Hollyit\LaravelJsonApi;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Hollyit\LaravelJsonApi\Filters\BaseFilter;
use Illuminate\Contracts\Translation\Translator;

class QueryValidator extends Validator
{
    /** @var array */
    protected $knownFilters = [];

    /**
     * QueryValidator constructor.
     *
     * @param  \Illuminate\Contracts\Translation\Translator  $translator
     * @param  array  $messages
     * @param  array  $customAttributes
     */
    public function __construct(
        Translator $translator,
        $messages = [],
        $customAttributes = []
    ) {
        parent::__construct($translator, ['filter' => []], [], $messages, $customAttributes);
    }

    /**
     * @return static
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function make()
    {
        return app()->make(static::class);
    }

    /**
     * @param  \Hollyit\LaravelJsonApi\Filters\BaseFilter  $filter
     */
    public function addFilter(BaseFilter $filter)
    {

        if (! isset($this->data['filter'])) {
            $this->data['filter'] = [];
        }

        $this->data['filter'][$filter->getKey()] = $filter->getValue();
        $this->addKnownFilter($filter->getKey());
        $filter->rules($this);
    }

    /**
     * @param $filter
     * @return $this
     */
    public function addKnownFilter($filter)
    {
        if (! in_array($filter, $this->knownFilters)) {
            $this->knownFilters[] = $filter;
        }

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function addUnknownFilter($name)
    {
        if (! in_array($name, $this->data['filter'])) {
            $this->data['filter'][] = $name;
        }

        return $this;
    }

    /**
     * @param $key
     * @param $rules
     * @return $this
     */
    public function addFilterRule($key, $rules)
    {
        $this->rules['filter.'.$key] = $rules;

        return $this;
    }

    /**
     * @param $value
     * @param $options
     * @return $this
     */
    public function addSortRule($value, $options)
    {
        $this->rules['sort'] = ['sometimes', Rule::in($options)];
        $this->data['sort'] = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function passes()
    {
        return parent::passes();
    }
}
