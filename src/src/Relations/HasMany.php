<?php

namespace Hollyit\LaravelJsonApi\Relations;

use Hollyit\LaravelJsonApi\ResourceResponse;

class HasMany extends Relation
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @param $includes
     * @param  \Hollyit\LaravelJsonApi\ResourceResponse  $response
     * @return array|mixed
     */
    public function toArray($resource, $includes, ResourceResponse $response)
    {
        $items = ['data' => []];
        $resources = $resource->getRelation($this->relationName);
        foreach ($resources as $item) {
            $schema = $response->api->getSchema($item);
            $identifier = [
                'type' => $schema->getType(),
                'id'   => $schema->getKey($item),
            ];
            $items['data'][] = $identifier;
            $response->addRelation($schema->getType(), $schema->getKey($item),
                $schema->toResource($item, $includes, $response));
        }

        return $items;
    }
}
