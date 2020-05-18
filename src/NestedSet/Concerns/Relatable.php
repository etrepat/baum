<?php

namespace Baum\NestedSet\Concerns;

trait Relatable
{
    /**
     * Parent relation (self-referential) 1-1.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(get_class($this), $this->getParentColumnName());
    }

    /**
     * Children relation (self-referential) 1-N.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function children()
    {
        return $this->hasMany(get_class($this), $this->getParentColumnName());
    }

    // /**
    //  * Inmmediate descendants relation. Alias for "children".
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\HasMany
    //  */
    // public function immediateDescendants()
    // {
    //     return $this->children();
    // }

    // /**
    //  * Attribute alias so as to eager-load the proper relationship.
    //  *
    //  * @return mixed
    //  */
    // public function getImmediateDescendantsAttribute()
    // {
    //     return $this->getRelationValue('children');
    // }

    /**
     * Retrive all of its "immediate" descendants.
     *
     * @param array   $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getImmediateDescendants($columns = ['*'])
    {
        // return $this->children()->get($columns);
        return $this->getRelationValue('children');
    }


    /**
     * Returns true if this is a root node.
     *
     * @return boolean
     */
    public function isRoot()
    {
        return is_null($this->getParentKey());
    }

    /**
     * Returns true if this is a leaf node (end of a branch).
     *
     * @return boolean
     */
    public function isLeaf()
    {
        return ($this->getRight() - $this->getLeft() === 1);
    }

    /**
     * Returns true if this is a child node.
     *
     * @return boolean
     */
    public function isChild()
    {
        return !$this->isRoot();
    }

    /**
     * Returns true if node is a descendant.
     *
     * @param NestedSet
     * @return boolean
     */
    public function isDescendantOf($other)
    {
        return (
            $this->getLeft() > $other->getLeft()  &&
            $this->getLeft() < $other->getRight() &&
            $this->inSameScope($other)
        );
    }

    /**
     * Returns true if node is self or a descendant.
     *
     * @param NestedSet
     * @return boolean
     */
    public function isSelfOrDescendantOf($other)
    {
        return (
            $this->getLeft() >= $other->getLeft() &&
            $this->getLeft() < $other->getRight() &&
            $this->inSameScope($other)
        );
    }

    /**
     * Returns true if node is an ancestor.
     *
     * @param NestedSet
     * @return boolean
     */
    public function isAncestorOf($other)
    {
        return (
            $this->getLeft() < $other->getLeft()  &&
            $this->getRight() > $other->getLeft() &&
            $this->inSameScope($other)
        );
    }

    /**
     * Returns true if node is self or an ancestor.
     *
     * @param NestedSet
     * @return boolean
     */
    public function isSelfOrAncestorOf($other)
    {
        return (
            $this->getLeft() <= $other->getLeft() &&
            $this->getRight() > $other->getLeft() &&
            $this->inSameScope($other)
        );
    }

    /**
     * Checks wether the given node is a descendant of itself. Basically, whether
     * its in the subtree defined by the left and right indices.
     *
     * @param \Baum\Node
     * @return boolean
     */
    public function insideSubtree($node)
    {
        return (
            $this->getLeft()  >= $node->getLeft()   &&
            $this->getLeft()  <= $node->getRight()  &&
            $this->getRight() >= $node->getLeft()   &&
            $this->getRight() <= $node->getRight()  &&
            $this->inSameScope($node)
        );
    }
}
