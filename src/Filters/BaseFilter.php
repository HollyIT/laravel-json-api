<?php

namespace Hollyit\LaravelJsonApi\Filters;

use Hollyit\LaravelJsonApi\QueryValidator;

abstract class BaseFilter
{
    /** @var string */
    protected $key;

    /**
     * @var null
     */
    protected $field;

    /** @var mixed */
    protected $default;

    /** @var bool */
    protected $multiple = false;

    /**
     * @var array
     */
    protected $value;

    /** @var string */
    protected $delimiter = ',';

    /** @var null|\Closure */
    protected $queryCallback;

    /**
     * BaseFilter constructor.
     *
     * @param $key
     * @param  null|\Closure  $field
     */
    public function __construct($key, $field = null)
    {
        $this->key = $key;
        if (is_callable($field)) {
            $this->queryCallback = $field;
            $this->field = $key;
        } else {
            $this->field = $field ?: $key;
        }
    }

    /**
     * @param $key
     * @param  null  $field
     * @return static
     */
    public static function make($key, $field = null)
    {
        return new static($key, $field);
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param  mixed  $default
     * @return BaseFilter
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param  mixed  $key
     * @return BaseFilter
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return null
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param  null  $field
     * @return BaseFilter
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        if ($this->isMultiple()) {
            $this->value = array_filter(array_map('trim', explode($this->delimiter, $value)));
        } else {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param  bool  $multiple
     * @return BaseFilter
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    abstract public function rules(QueryValidator $validator);

    /**
     * @param  \Closure | null  $callback
     * @return $this
     */
    public function queryCallback($callback)
    {
        $this->queryCallback = $callback;

        return $this;
    }

    /**
     * @param  \Illuminate\Database\Query\Builder | \Illuminate\Database\Eloquent\Builder  $builder
     */
    public function query($builder)
    {
        if ($this->hasValue()) {
            if (is_callable($this->queryCallback)) {
                call_user_func($this->queryCallback, $builder, $this);
            } else {
                $this->doQuery($builder);
            }
        }
    }

    /**
     * @return bool
     */
    public function hasValue()
    {
        return ! empty($this->value);
    }

    /**
     * @param  \Illuminate\Database\Query\Builder | \Illuminate\Database\Eloquent\Builder  $builder
     */
    public function doQuery($builder)
    {
        if ($this->multiple) {
            $builder->where($this->field, 'in', $this->value);
        } else {
            $builder->where($this->field, $this->value);
        }
    }
}
