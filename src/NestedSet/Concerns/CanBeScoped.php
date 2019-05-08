<?php

namespace Baum\NestedSet\Concerns;

trait CanBeScoped
{
    /**
     * Returns wether this particular node instance is scoped by certain fields
     * or not.
     *
     * @return boolean
     */
    public function isScoped()
    {
        return !!(count($this->getScopedColumnNames()) > 0);
    }

    /**
     * Checkes if the given node is in the same scope as the current one.
     *
     * @param \Baum\Node
     * @return boolean
     */
    public function inSameScope($other)
    {
        foreach ($this->getScopedColumnNames() as $fld) {
            if ($this->getAttribute($fld) != $other->getAttribute($fld)) {
                return false;
            }
        }

        return true;
    }
}
