<?php

 namespace Baum\NestedSet\Concerns;

trait HasAttributes
{
    /**
    * Get the value of the model's "parent_id" field.
    *
    * @return mixed
    */
    public function getParentKey()
    {
        return $this->getAttribute($this->getParentColumnName());
    }

    /**
     * Get the value of the model's "left" field.
     *
     * @return int
     */
    public function getLeft()
    {
        return (int) $this->getAttribute($this->getLeftColumnName());
    }

    /**
     * Get the value of the model's "right" field.
     *
     * @return int
     */
    public function getRight()
    {
        return (int) $this->getAttribute($this->getRightColumnName());
    }

    /**
     * Get the model's "depth" value.
     *
     * @return int
     */
    public function getDepth()
    {
        return (int) $this->getAttribute($this->getDepthColumnName());
    }

    /**
     * Get the model's "order" value.
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->getAttribute($this->getOrderColumnName());
    }
}
