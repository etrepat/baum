<?php

namespace Baum\NestedSet\Concerns;

use Baum\NestedSet\Mapper;

trait Mappable
{
    /**
     * Maps the provided tree structure into the database using the current node
     * as the parent. The provided tree structure will be inserted/updated as the
     * descendancy subtree of the current node instance.
     *
     * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
     * @return  boolean
     */
    public function makeTree($nodeList)
    {
        $mapper = new Mapper($this);

        return $mapper->map($nodeList);
    }

    /**
     * Maps the provided tree structure into the database.
     *
     * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
     * @return  boolean
     */
    public static function buildTree($nodeList)
    {
        return with(new static)->makeTree($nodeList);
    }
}
