<?php

namespace Hollyit\LaravelJsonApi\Filters;

use Hollyit\LaravelJsonApi\QueryValidator;

class SearchFilter extends BaseFilter
{
    /** @var array */
    protected $searchFields = [];

    public function __construct($key, $field = null)
    {
        parent::__construct($key, $field);
        $this->searchFields[] = $field;
    }

    public function addSearchField($fields)
    {
        $fields = is_array($fields) ? $fields : [$fields];
        foreach ($fields as $field) {
            if (! in_array($field, $this->searchFields)) {
                $this->searchFields[] = $field;
            }
        }

        return $this;
    }

    public function rules(QueryValidator $validator)
    {
        $validator->addFilterRule($this->key, ['sometimes', 'string']);
    }

    public function doQuery($builder)
    {
        $builder->where(function ($sq) {
            /** @var \Illuminate\Database\Eloquent\Builder $sq */
            $sq->orWhere($this->field, 'LIKE', '%'.$this->value.'%');
            foreach ($this->searchFields as $field) {
                $sq->orWhere($field, 'LIKE', '%'.$this->value.'%');
            }
        });
    }
}
