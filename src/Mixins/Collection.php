<?php

namespace Baum\Mixins;

class Collection
{
    /**
     * Exports the current collection instance to a nested hierarchy.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function toHierarchy()
    {
        return function () {
            $dict = $this->sortBy(function ($node) {
                return $node->getOrder();
            })->each(function ($node) {
                $node->setRelation('children', new static);
            })->getDictionary();

            $nestedKeys = [];

            foreach ($dict as $key => $node) {
                $parentKey = $node->getParentKey();

                if (!is_null($parentKey) && array_key_exists($parentKey, $dict)) {
                    $dict[$parentKey]->children[] = $node;

                    $nestedKeys[] = $node->getKey();
                }
            }

            foreach ($nestedKeys as $key) {
                unset($dict[$key]);
            }

            return new static($dict);
        };
    }
}
