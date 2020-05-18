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
            $this->integer('parent_id')->unsigned()->nullable()->index();
            $this->foreign('parent_id')->references('id')->on($this->getTable());
            $this->integer('left')->unsigned()->nullable()->index();
            $this->integer('right')->unsgined()->nullable()->index();
            $this->integer('depth')->unsigned()->nullable()->index();
        };
    }
}
