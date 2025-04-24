<?php

namespace Baum\NestedSet\Concerns;

trait HasColumns
{
    /**
     * Get the parent column name.
     *
     * @return string
     */
    public function getParentColumnName()
    {
        if (!property_exists($this, 'parentColumnName')) {
            return $this->getDefaultParentColumnName();
        }

        return $this->parentColumnName;
    }

    /**
     * Get the "default" parent column name.
     *
     * @return string
     */
    public function getDefaultParentColumnName()
    {
        return 'parent_id';
    }

    /**
     * Get the table qualified parent column name.
     *
     * @return string
     */
    public function getQualifiedParentColumnName()
    {
        return $this->qualifyColumn($this->getParentColumnName());
    }

    /**
     * Get the "left" field column name.
     *
     * @return string
     */
    public function getLeftColumnName()
    {
        if (!property_exists($this, 'leftColumnName')) {
            return $this->getDefaultLeftColumnName();
        }

        return $this->leftColumnName;
    }

    /**
     * Get the "default" left column name.
     *
     * @return string
     */
    public function getDefaultLeftColumnName()
    {
        return 'left';
    }

    /**
     * Get the table qualified "left" field column name.
     *
     * @return string
     */
    public function getQualifiedLeftColumnName()
    {
        return $this->qualifyColumn($this->getLeftColumnName());
    }

    /**
     * Get the "right" field column name.
     *
     * @return string
     */
    public function getRightColumnName()
    {
        if (!property_exists($this, 'rightColumnName')) {
            return $this->getDefaultRightColumnName();
        }

        return $this->rightColumnName;
    }

    /**
     * Get the "default" right column name.
     *
     * @return string
     */
    public function getDefaultRightColumnName()
    {
        return 'right';
    }

    /**
     * Get the table qualified "right" field column name.
     *
     * @return string
     */
    public function getQualifiedRightColumnName()
    {
        return $this->qualifyColumn($this->getRightColumnName());
    }

    /**
     * Get the "depth" field column name.
     *
     * @return string
     */
    public function getDepthColumnName()
    {
        if (!property_exists($this, 'depthColumnName')) {
            return $this->getDefaultDepthColumnName();
        }

        return $this->depthColumnName;
    }

    /**
     * Get the "default" depth column name.
     *
     * @return string
     */
    public function getDefaultDepthColumnName()
    {
        return 'depth';
    }

    /**
     * Get the table qualified "depth" field column name.
     *
     * @return string
     */
    public function getQualifiedDepthColumnName()
    {
        return $this->qualifyColumn($this->getDepthColumnName());
    }

    /**
     * Get the "order" field column name.
     *
     * @return string
     */
    public function getOrderColumnName()
    {
        if (!property_exists($this, 'orderColumnName')) {
            return $this->getLeftColumnName();
        }

        return $this->orderColumnName ?? $this->getLeftColumnName();
    }

    /**
     * Get the table qualified "order" field column name.
     *
     * @return string
     */
    public function getQualifiedOrderColumnName()
    {
        return $this->qualifyColumn($this->getOrderColumnName());
    }

    /**
     * Get the column names which define our scope
     *
     * @return array
     */
    public function getScopedColumnNames()
    {
        if (!property_exists($this, 'scopeColumnNames')) {
            return [];
        }

        return (array) $this->scopeColumnNames;
    }

    /**
     * Get the qualified column names which define our scope
     *
     * @return array
     */
    public function getQualifiedScopedColumnNames()
    {
        return array_map(function ($c) {
            return $this->qualifyColumn($c);
        }, $this->getScopedColumnNames());
    }
}
