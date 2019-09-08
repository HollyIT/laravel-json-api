<?php

namespace Hollyit\LaravelJsonApi;

use App\BaseSchema;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Hollyit\LaravelJsonApi\Relations\HasOne;
use Hollyit\LaravelJsonApi\Relations\HasMany;
use Hollyit\LaravelJsonApi\Relations\Relation;
use Hollyit\LaravelJsonApi\Filters\BaseFilter;
use Hollyit\LaravelJsonApi\Exceptions\UnknownRelationException;

abstract class Schema
{
    /**
     * @var \Hollyit\LaravelJsonApi\JsonApi
     */
    protected $api;

    /**
     * @var Relation[]
     */
    protected $relations = [];

    /**
     * @var \Illuminate\Support\ | BaseFilter[]
     */
    protected $filters;

    protected $sorts = [];

    protected $defaultSort = null;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $instance;

    public function __construct(JsonApi $api)
    {
        $this->api = $api;
        $this->instance = $this->getInstance();
        $this->filters = collect([]);
        $this->getFilters();
        $this->getRelations();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract public function getInstance();

    abstract public function getFilters();

    abstract public function getRelations();

    /**
     * @param $sort
     * @param  null  $field
     * @return $this
     */
    public function addSort($sort, $field = null)
    {
        $field = $field ?: $sort;
        $this->sorts[$sort] = $field;

        return $this;
    }

    /**
     * @param  \Hollyit\LaravelJsonApi\Filters\BaseFilter  $filter
     * @return $this
     */
    public function addFilter(BaseFilter $filter)
    {
        $this->filters->put($filter->getKey(), $filter);

        return $this;
    }

    /**
     * @param $resourceClass
     * @param $name
     * @param  null  $relationName
     * @return \Hollyit\LaravelJsonApi\Relations\HasOne
     */
    public function hasOne($resourceClass, $name, $relationName = null)
    {
        $relationName = $relationName ?: $name;
        $instance = new HasOne($resourceClass, $name, $relationName, $this);
        $this->addRelation($instance);

        return $instance;
    }

    /**
     * @param  \Hollyit\LaravelJsonApi\Relations\Relation  $relation
     * @return $this
     */
    public function addRelation(Relation $relation)
    {
        $this->relations[$relation->name] = $relation;

        return $this;
    }

    /**
     * @param $resourceClass
     * @param $name
     * @param  null  $relationName
     * @return \Hollyit\LaravelJsonApi\Relations\HasMany
     */
    public function hasMany($resourceClass, $name, $relationName = null)
    {
        $relationName = $relationName ?: $name;
        $instance = new HasMany($resourceClass, $name, $relationName, $this);
        $this->addRelation($instance);

        return $instance;
    }

    /**
     * @param $resource
     * @param  \Illuminate\Support\Collection  $includes
     * @param  \Hollyit\LaravelJsonApi\ResourceResponse  $response
     * @return array
     */
    public function toResource($resource, Collection $includes, ResourceResponse $response)
    {

        $data = [
            'type'          => $this->getType(),
            'id'            => $this->getKey($resource),
            'attributes'    => $this->getAttributes($resource),
            'relationships' => [],
        ];
        foreach ($includes as $include) {
            if ($this->hasRelation($include)) {

                $relationIncludes = collect([]);
                foreach ($includes as $item) {
                    if (Str::startsWith($item, $include.'.')) {
                        $name = Str::replaceFirst($include.'.', '', $item);
                        $relationIncludes->push($name);
                    }
                }
                $data['relationships'][$include] = $this->relations[$include]->toArray($resource, $relationIncludes,
                    $response);
            }
        }

        return $data;
    }

    abstract public function getType();

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @return mixed
     */
    public function getKey($resource)
    {
        return $resource->getKey();
    }

    abstract public function getAttributes($resource);

    /**
     * @param $relation
     * @return bool
     */
    public function hasRelation($relation)
    {
        return isset($this->relations[$relation]);
    }

    public function addIncludes($includes, $builder, $prefix = '')
    {
        foreach ($includes as $include) {
            echo $include.PHP_EOL;
            if (stripos($include, '.')) {
                echo PHP_EOL;
                list($resource, $trail) = explode('.', $include, 2);
                $relation = $this->getRelation($resource);
                if ($relation) {
                    $schema = $this->api->getSchema($relation->getResourceClass());
                    $prefix .= $relation->getRelationName().'.';
                    $schema->addIncludes([$trail], $builder, $prefix);
                }
            } elseif ($relation = $this->getRelation($include)) {
                $relation->addInclude($builder, $prefix);
            }
        }
    }

    public function prepareIncludes($includes)
    {
        $items = [];
        foreach ($includes as $include) {
            Arr::set($items, $include, $include);
        }

        return $this->gatherIncludes($items, $this);
    }

    protected function gatherIncludes($includes, Schema $schema, $prefix = '')
    {

        $results = [];

        foreach ($includes as $name => $children) {
            $relation = $schema->getRelation($name);
            if ($relation) {
                $name = $relation->getRelationName();
                $results[] = $prefix.$name;
                if (is_array($children)) {
                    $newSchema = $this->api->getSchema($relation->getResourceClass());
                    $cResults = $this->gatherIncludes($children, $newSchema, $prefix.$name.'.');
                    $results = array_merge($results, $cResults);
                }
            }
        }

        return $results;
    }

    /**
     * @param  \Hollyit\LaravelJsonApi\ResourceCollectionResponse  $resource
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function createBuilder(ResourceCollectionResponse $resource)
    {

        $includes = $this->prepareIncludes($resource->includes);

        $builder = $this->getBuilder($resource);
        $builder->with($includes);
        foreach ($this->filters as $filter) {
            $filter->query($builder);
        }

        $this->querySort($builder, $resource->getSort());
        // do Filters
        // do Sorts

        return $builder;
    }

    /**
     * @param $resource
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder($resource)
    {
        return $this->instance->newQuery();
    }

    public function loadRelation($relation, $resource, $prefix = '')
    {
        if (stripos($relation, '.')) {

            list($resourceName, $trail) = explode('.', $relation, 2);
            $relationInstance = $this->getRelation($resourceName);

            if ($relationInstance) {

                $schema = $this->api->getSchema($relationInstance->getResourceClass());

                $prefix .= $relationInstance->getRelationName().'.';

                $schema->loadRelation($trail, $resource, $prefix);
            }
        } else {
            echo "l->$relation ";
            $this->getRelation($relation)
                ->loadRelation($resource, $prefix);
        }
    }

    /**
     * @param $name
     * @return \Hollyit\LaravelJsonApi\Relations\Relation|null
     */
    public function getRelation($name)
    {
        return $this->hasRelation($name) ? $this->relations[$name] : null;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param $sort
     * @return \Hollyit\LaravelJsonApi\Schema
     */
    protected function querySort($builder, $sort)
    {
        $sort = $sort ?: $this->defaultSort;
        if ($sort) {
            $sortParts = $this->parseSortData($sort);

            $builder->orderBy($sortParts[0], $sortParts[1]);
        }

        return $this;
    }

    /**
     * @param $value
     * @return array
     */
    protected function parseSortData($value)
    {
        $dir = 'asc';
        $field = $value;
        if (substr($value, 0, 1) === '-') {
            $dir = 'desc';
            $field = substr($value, 1);
        }

        return [$field, $dir];
    }

    /**
     * @param $data
     * @return \Hollyit\LaravelJsonApi\QueryValidator
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function validate($data)
    {
        $validator = QueryValidator::make();
        $this->getFilters();
        if (isset($data['filters'])) {
            $this->addFilterValidation($data['filters'], $validator);
        }

        if (! empty($data['includes'])) {
            $this->addIncludeValidation($data['includes'], $validator);
        }

        if (! empty($data['sort'])) {
            $this->addSortValidation($data['sort'], $validator);
        }

        return $validator;
    }

    /**
     * @param $data
     * @param  \Hollyit\LaravelJsonApi\QueryValidator  $validator
     * @return $this
     */
    protected function addFilterValidation($data, QueryValidator $validator)
    {

        foreach ($data as $key => $value) {
            if ($filter = $this->getFilter($key)) {
                $filter->setValue($value);
                $validator->addFilter($filter);
            } else {
                $validator->addUnknownFilter($key);
            }
        }
        foreach ($this->filters as $filter) {
            $validator->addKnownFilter($filter->getKey());
        }

        return $this;
    }

    /**
     * @param $name
     * @return \Hollyit\LaravelJsonApi\Filters\BaseFilter|mixed|null
     */
    public function getFilter($name)
    {
        return $this->hasFilter($name) ? $this->filters[$name] : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasFilter($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * @param $includes
     * @param  \Hollyit\LaravelJsonApi\QueryValidator  $validator
     * @return $this
     */
    protected function addIncludeValidation($includes, QueryValidator $validator)
    {
        $validator->after(function (QueryValidator $validator) use ($includes) {
            $items = [];
            foreach ($includes as $include) {
                Arr::set($items, $include, true);
            }
            try {
                $this->validateIncludes($items);
            } catch (UnknownRelationException $e) {
                $validator->getMessageBag()
                    ->add('includes', $e->getMessage());
            }
        });

        return $this;
    }

    /**
     * @param $includes
     * @throws \Hollyit\LaravelJsonApi\Exceptions\UnknownRelationException
     */
    public function validateIncludes($includes)
    {
        foreach ($includes as $include => $children) {
            if (! $this->hasRelation($include)) {
                throw new UnknownRelationException($include);
            }
            if (is_array($children) && $schemaClass = $this->relations[$include]->getResourceClass()) {
                $schema = $this->api->getSchema($schemaClass);
                try {
                    $schema->validateIncludes($children);
                } catch (UnknownRelationException $e) {
                    throw new UnknownRelationException($include.'.'.$e->getInclude());
                }
            }
        }
    }

    /**
     * @param $sort
     * @param  \Hollyit\LaravelJsonApi\QueryValidator  $validator
     * @return $this
     */
    protected function addSortValidation($sort, QueryValidator $validator)
    {
        $options = [];
        foreach (array_keys($this->sorts) as $key) {
            $options[] = $key;
            $options[] = '-'.$key;
        }
        $validator->addSortRule($sort, $options);

        return $this;
    }
}
