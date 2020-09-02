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

    /**
     * Inmmediate descendants relation. Alias for "children".
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function immediateDescendants()
    {
        return $this->children();
    }

    /**
     * Retrive all of its "immediate" descendants.
     *
     * @param array   $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getImmediateDescendants($columns = ['*'])
    {
        return $this->getRelationValue('children');
    }

    /**
     * Equality test.
     *
     * @param \Baum\NestedSet\Node
     * @return boolean
     */
    public function equals($other)
    {
        return ($this == $other);
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
     * Returns true if this is a trunk node (not root or leaf).
     *
     * @return boolean
     */
    public function isTrunk()
    {
        return !$this->isRoot() && !$this->isLeaf();
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

    /**
     * Instance scope which targes all the ancestor chain nodes including
     * the current one.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function ancestorsAndSelf()
    {
        return $this->newQuery()
                ->where($this->getLeftColumnName(), '<=', $this->getLeft())
                ->where($this->getRightColumnName(), '>=', $this->getRight());
    }

    /**
     * Get all the ancestor chain from the database including the current node.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAndSelf($columns = ['*'])
    {
        return $this->ancestorsAndSelf()->get($columns);
    }

    /**
     * Get all the ancestor chain from the database including the current node
     * but without the root node.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAndSelfWithoutRoot($columns = ['*'])
    {
        return $this->ancestorsAndSelf()->withoutRoot()->get($columns);
    }

    /**
     * Instance scope which targets all the ancestor chain nodes excluding
     * the current one.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function ancestors()
    {
        return $this->ancestorsAndSelf()->withoutSelf();
    }

    /**
     * Get all the ancestor chain from the database excluding the current node.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors($columns = ['*'])
    {
        return $this->ancestors()->get($columns);
    }

    /**
     * Get all the ancestor chain from the database excluding the current node
     * and the root node (from the current node's perspective).
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsWithoutRoot($columns = ['*'])
    {
        return $this->ancestors()->withoutRoot()->get($columns);
    }

    /**
     * Instance scope which targets all children of the parent, including self.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function siblingsAndSelf()
    {
        return $this->newQuery()
                ->where($this->getParentColumnName(), $this->getParentKey());
    }

    /**
     * Get all children of the parent, including self.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblingsAndSelf($columns = ['*'])
    {
        return $this->siblingsAndSelf()->get($columns);
    }

    /**
     * Instance scope targeting all children of the parent, except self.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function siblings()
    {
        return $this->siblingsAndSelf()->withoutSelf();
    }

    /**
     * Return all children of the parent, except self.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings($columns = ['*'])
    {
        return $this->siblings()->get($columns);
    }

    /**
     * Instance scope targeting all of its nested children which do not have
     * children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function leaves()
    {
        $grammar = $this->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($this->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($this->getQualifiedLeftColumnName());

        return $this->descendants()
                ->whereRaw($rgtCol . ' - ' . $lftCol . ' = 1');
    }

    /**
     * Return all of its nested children which do not have children.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLeaves($columns = ['*'])
    {
        return $this->leaves()->get($columns);
    }

    /**
     * Instance scope targeting all of its nested children which are between the
     * root and the leaf nodes (middle branch).
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function trunks()
    {
        $grammar = $this->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($this->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($this->getQualifiedLeftColumnName());

        return $this->descendants()
                ->whereNotNull($this->getQualifiedParentColumnName())
                ->whereRaw($rgtCol . ' - ' . $lftCol . ' != 1');
    }

    /**
     * Return all of its nested children which are trunks.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrunks($columns = ['*'])
    {
        return $this->trunks()->get($columns);
    }

    /**
     * Scope targeting itself and all of its nested children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function descendantsAndSelf()
    {
        return $this->newQuery()
                ->where($this->getLeftColumnName(), '>=', $this->getLeft())
                ->where($this->getLeftColumnName(), '<', $this->getRight());
    }

    /**
     * Retrieve all nested children an self.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendantsAndSelf($columns = ['*'])
    {
        if (is_array($columns)) {
            return $this->descendantsAndSelf()->get($columns);
        }

        $arguments = func_get_args();

        $limit    = intval(array_shift($arguments));
        $columns  = array_shift($arguments) ?: ['*'];

        return $this->descendantsAndSelf()->limitDepth($limit)->get($columns);
    }

    /**
     * Set of all children & nested children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function descendants()
    {
        return $this->descendantsAndSelf()->withoutSelf();
    }

    /**
     * Retrieve all of its children & nested children.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants($columns = ['*'])
    {
        if (is_array($columns)) {
            return $this->descendants()->get($columns);
        }

        $arguments = func_get_args();

        $limit    = intval(array_shift($arguments));
        $columns  = array_shift($arguments) ?: ['*'];

        return $this->descendants()->limitDepth($limit)->get($columns);
    }

    /**
     * Returns the first sibling to the left.
     *
     * @return NestedSet
     */
    public function getLeftSibling()
    {
        return $this->siblings()
                ->where($this->getLeftColumnName(), '<', $this->getLeft())
                ->orderBy($this->getOrderColumnName(), 'desc')
                ->first();
    }

    /**
     * Returns the first sibling to the right.
     *
     * @return NestedSet
     */
    public function getRightSibling()
    {
        return $this->siblings()
                ->where($this->getLeftColumnName(), '>', $this->getLeft())
                ->first();
    }

    /**
     * Prunes a branch off the tree, shifting all the elements on the right
     * back to the left so the counts work.
     *
     * @return void;
     */
    public function destroyDescendants()
    {
        if (is_null($this->getRight()) || is_null($this->getLeft())) {
            return;
        }

        $this->getConnection()->transaction(function () {
            $this->refresh();

            $lftCol = $this->getQualifiedLeftColumnName();
            $rgtCol = $this->getQualifiedRightColumnName();
            $lft    = $this->getLeft();
            $rgt    = $this->getRight();

            // Apply a lock to the rows which fall past the deletion point
            $this->newQuery()->where($lftCol, '>=', $lft)->select($this->getQualifiedKeyName())->lockForUpdate()->get();

            // Prune children
            $this->newQuery()->where($lftCol, '>', $lft)->where($rgtCol, '<', $rgt)->delete();

            // Update left and right indexes for the remaining nodes
            $diff = $rgt - $lft + 1;

            $this->newQuery()->where($lftCol, '>', $rgt)->decrement($lftCol, $diff);
            $this->newQuery()->where($rgtCol, '>', $rgt)->decrement($rgtCol, $diff);
        });
    }
}
