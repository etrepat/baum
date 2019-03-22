<?php

namespace Baum\NestedSet;

trait Columns {
    /**
     * Get the parent column name.
    *
    * @return string
    */
    public function getParentColumnName() {
        return $this->parentColumn;
    }

    /**
     * Get the table qualified parent column name.
    *
    * @return string
    */
    public function getQualifiedParentColumnName() {
        $this->qualifyColumn($this->getParentColumnName());
    }

    /**
     * Get the "left" field column name.
     *
     * @return string
     */
    public function getLeftColumnName() {
        return $this->leftColumn;
    }

    /**
     * Get the table qualified "left" field column name.
     *
     * @return string
     */
    public function getQualifiedLeftColumnName() {
        return $this->qualifyColumn($this->getLeftColumnName());
    }

    /**
     * Get the value of the model's "left" field.
     *
     * @return int
     */
    public function getLeft() {
    return $this->getAttribute($this->getLeftColumnName());
    }

    /**
     * Get the "right" field column name.
     *
     * @return string
     */
    public function getRightColumnName() {
    return $this->rightColumn;
    }

    /**
     * Get the table qualified "right" field column name.
     *
     * @return string
     */
    public function getQualifiedRightColumnName() {
    return $this->getTable() . '.' . $this->getRightColumnName();
    }

    /**
     * Get the value of the model's "right" field.
     *
     * @return int
     */
    public function getRight() {
    return $this->getAttribute($this->getRightColumnName());
    }

    /**
     * Get the "depth" field column name.
     *
     * @return string
     */
    public function getDepthColumnName() {
    return $this->depthColumn;
    }

    /**
     * Get the table qualified "depth" field column name.
     *
     * @return string
     */
    public function getQualifiedDepthColumnName() {
    return $this->getTable() . '.' . $this->getDepthColumnName();
    }

    /**
     * Get the model's "depth" value.
     *
     * @return int
     */
    public function getDepth() {
    return $this->getAttribute($this->getDepthColumnName());
    }

    /**
     * Get the "order" field column name.
     *
     * @return string
     */
    public function getOrderColumnName() {
    return is_null($this->orderColumn) ? $this->getLeftColumnName() : $this->orderColumn;
    }

    /**
     * Get the table qualified "order" field column name.
     *
     * @return string
     */
    public function getQualifiedOrderColumnName() {
    return $this->getTable() . '.' . $this->getOrderColumnName();
    }

    /**
     * Get the model's "order" value.
     *
     * @return mixed
     */
    public function getOrder() {
    return $this->getAttribute($this->getOrderColumnName());
    }

    /**
     * Get the column names which define our scope
     *
     * @return array
     */
    public function getScopedColumns() {
    return (array) $this->scoped;
    }

    /**
     * Get the qualified column names which define our scope
     *
     * @return array
     */
    public function getQualifiedScopedColumns() {
    if ( !$this->isScoped() )
        return $this->getScopedColumns();

    $prefix = $this->getTable() . '.';

    return array_map(function($c) use ($prefix) {
        return $prefix . $c; }, $this->getScopedColumns());
    }

}
