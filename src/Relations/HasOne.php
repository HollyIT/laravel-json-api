<?php

namespace Hollyit\LaravelJsonApi\Relations;

use Hollyit\LaravelJsonApi\ResourceResponse;

class HasOne extends Relation
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @param $includes
     * @param  \Hollyit\LaravelJsonApi\ResourceResponse  $response
     * @return array|mixed
     */
    public function toArray($resource, $includes, ResourceResponse $response)
    {
        $items = ['data' => null];
        $relation = $resource->getRelation($this->relationName);
        if ($relation) {
            $schema = $response->api->getSchema($relation);
            $identifier = [
                'type' => $schema->getType(),
                'id'   => $schema->getKey($relation),
            ];

            $items['data'] = $identifier;
            $response->addRelation($schema->getType(), $schema->getKey($relation),
                $schema->toResource($relation, $includes, $response));
        }
        return $items;
    }
}
