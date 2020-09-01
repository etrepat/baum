<?php

namespace Baum\Mixins;

class Blueprint
{
    /**
     * Add helper method to add the nested set structure related fields to
     * an schema.
     *
     * @return void
     */
    public function nestedSet()
    {
        return function () {
            $this->unsignedBigInteger('parent_id')->nullable()->index();
            $this->foreign('parent_id')->references('id')->on($this->getTable());
            $this->unsignedBigInteger('left')->nullable()->index();
            $this->unsignedBigInteger('right')->nullable()->index();
            $this->unsignedInteger('depth')->nullable()->index();
        };
    }
}
