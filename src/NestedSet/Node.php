<?php

namespace Baum\NestedSet;

/**
 * Node
 *
 * This trait implements Nested Set functionality. A Nested Set is a
 * smart way to implement an ordered tree with the added benefit that you can
 * select all of their descendants with a single query. Drawbacks are that
 * insertion or move operations need more complex sql queries.
 *
 * Nested sets are appropiate when you want either an ordered tree (menus,
 * commercial categories, etc.) or an efficient way of querying big trees.
 */
trait Node
{
    use Concerns\HasColumns,
        Concerns\HasAttributes,
        Concerns\HasEvents,
        Concerns\WorksWithSoftDeletes,
        Concerns\CanBeScoped,
        Concerns\Relatable,
        Concerns\Movable,
        Concerns\Validatable,
        Concerns\Rebuildable,
        Concerns\Mappable;

    /**
     * Boot the nested set node trait for a model.
     *
     * @return void
     */
    public static function bootNode()
    {
        static::addGlobalScope(new Scopes\OrderingScope);

        static::addGlobalScope(new Scopes\ScopedByScope);
    }

    /**
     * Return a new query object *without* the nested set global scopes applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQueryWithoutNestedSetScopes()
    {
        return $this->newQuery()
                    ->withoutGlobalScope(Scopes\OrderingScope::class)
                    ->withoutGlobalScope(Scopes\ScopedByScope::class);
    }

    /**
     * Indicates whether we should move to a new parent.
     *
     * @var int
     */
    protected static $moveToNewParentId = null;

    /**
     * Returns the first root node.
     *
     * @return \Baum\NestedSet\Node
     */
    public static function root()
    {
        return static::roots()->first();
    }

    /**
     * Static query scope. Returns a query scope with all root nodes.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function roots()
    {
        $instance = new static;

        return $instance->newQuery()->whereNull($instance->getParentColumnName());
    }

    /**
    * Returns the level of this node in the tree.
    * Root level is 0.
    *
    * @return int
    */
    public function getLevel()
    {
        if (is_null($this->getParentKey())) {
            return 0;
        }

        return $this->computeLevel();
    }

    /**
     * Compute current node level. If could not move past ourseleves return
     * our ancestor count, otherwhise get the first parent level + the computed
     * nesting.
     *
     * @return integer
     */
    protected function computeLevel()
    {
        list($node, $nesting) = $this->determineDepth($this);

        if ($node->equals($this)) {
            return $this->ancestors()->count();
        }

        return $node->getLevel() + $nesting;
    }

    /**
     * Return an array with the last node we could reach and its nesting level
     *
     * @param   Baum\Node $node
     * @param   integer   $nesting
     * @return  array
     */
    protected function determineDepth($node, $nesting = 0)
    {
        // Traverse back up the ancestry chain and add to the nesting level count
        while ($parent = $node->parent()->first()) {
            $nesting = $nesting + 1;

            $node = $parent;
        }

        return [$node, $nesting];
    }

    /**
     * Sets default values for left and right fields.
     *
     * @return void
     */
    public function setDefaultLeftAndRight()
    {
        $maxRgt = (int) $this->newQuery()->max($this->getQualifiedRightColumnName());

        $this->setAttribute($this->getLeftColumnName(), $maxRgt + 1);
        $this->setAttribute($this->getRightColumnName(), $maxRgt + 2);
    }

    /**
     * Store the parent_id if the attribute is modified so as we are able to move
     * the node to this new parent after saving.
     *
     * @return void
     */
    public function storeNewParent()
    {
        if ($this->isDirty($this->getParentColumnName()) && ($this->exists || !$this->isRoot())) {
            static::$moveToNewParentId = $this->getParentKey();
        } else {
            static::$moveToNewParentId = false;
        }
    }

    /**
     * Move to the new parent if appropiate.
     *
     * @return void
     */
    public function moveToNewParent()
    {
        $pid = static::$moveToNewParentId;

        if (is_null($pid)) {
            $this->makeRoot();
        } elseif ($pid !== false) {
            $this->makeChildOf($pid);
        }
    }

    /**
     * Sets the depth attribute
     *
     * @return \Baum\Node
     */
    public function setDepth()
    {
        $this->getConnection()->transaction(function () {
            $this->refresh();

            $level = $this->getLevel();

            $this->newQuery()->where($this->getKeyName(), '=', $this->getKey())->update([$this->getDepthColumnName() => $level]);
            $this->setAttribute($this->getDepthColumnName(), $level);
        });

        return $this;
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

    /**
     * Static query scope. Returns a query scope with all nodes which are at
     * the end of a branch.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function allLeaves()
    {
        $instance = new static;

        $grammar = $instance->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($instance->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($instance->getQualifiedLeftColumnName());

        return $instance->newQuery()
                    ->whereRaw($rgtCol . ' - ' . $lftCol . ' = 1')
                    ->orderBy($instance->getQualifiedOrderColumnName());
    }

    /**
     * Static query scope. Returns a query scope with all nodes which are at
     * the middle of a branch (not root and not leaves).
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function allTrunks()
    {
        $instance = new static;

        $grammar = $instance->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($instance->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($instance->getQualifiedLeftColumnName());

        return $instance->newQuery()
                    ->whereNotNull($instance->getParentColumnName())
                    ->whereRaw($rgtCol . ' - ' . $lftCol . ' != 1')
                    ->orderBy($instance->getQualifiedOrderColumnName());
    }

    /**
     * Query scope which extracts a certain node object from the current query
     * expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutNode($query, $node)
    {
        return $query->where($node->getKeyName(), '!=', $node->getKey());
    }

    /**
     * Extracts current node (self) from current query expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutSelf($query)
    {
        return $this->scopeWithoutNode($query, $this);
    }

    /**
     * Extracts first root (from the current node p-o-v) from current query
     * expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutRoot($query)
    {
        return $this->scopeWithoutNode($query, $this->getRoot());
    }

    /**
     * Provides a depth level limit for the query.
     *
     * @param   query   \Illuminate\Database\Query\Builder
     * @param   limit   integer
     * @return  \Illuminate\Database\Query\Builder
     */
    public function scopeLimitDepth($query, $limit)
    {
        $depth  = $this->exists ? $this->getDepth() : $this->getLevel();
        $max    = $depth + $limit;
        $scopes = [$depth, $max];

        return $query->whereBetween($this->getDepthColumnName(), [min($scopes), max($scopes)]);
    }

    /**
     * Returns the root node starting at the current node.
     *
     * @return NestedSet
     */
    public function getRoot()
    {
        if ($this->exists) {
            return $this->ancestorsAndSelf()->whereNull($this->getParentColumnName())->first();
        } else {
            $parentId = $this->getParentKey();

            if (!is_null($parentId) && $currentParent = static::find($parentId)) {
                return $currentParent->getRoot();
            } else {
                return $this;
            }
        }
    }

    /**
     * Sets the depth attribute for the current node and all of its descendants.
     *
     * @return \Baum\Node
     */
    public function setDepthWithSubtree()
    {
        $this->getConnection()->transaction(function () {
            $this->refresh();

            $this->descendantsAndSelf()->select($this->getKeyName())->lockForUpdate()->get();

            $oldDepth = !is_null($this->getDepth()) ? $this->getDepth() : 0;

            $newDepth = $this->getLevel();

            $this->newQuery()->where($this->getKeyName(), '=', $this->getKey())->update([$this->getDepthColumnName() => $newDepth]);
            $this->setAttribute($this->getDepthColumnName(), $newDepth);

            $diff = $newDepth - $oldDepth;
            if (!$this->isLeaf() && $diff != 0) {
                $this->descendants()->increment($this->getDepthColumnName(), $diff);
            }
        });

        return $this;
    }

    /**
     * Return an key-value array indicating the node's depth with $seperator
     *
     * @return Array
     */
    public static function getNestedList($column, $key = null, $seperator = ' ')
    {
        $instance = new static;

        $key = $key ?: $instance->getKeyName();
        $depthColumn = $instance->getDepthColumnName();

        $nodes = $instance->newQuery()->get()->toArray();

        return array_combine(array_map(function ($node) use ($key) {
            return $node[$key];
        }, $nodes), array_map(function ($node) use ($seperator, $depthColumn, $column) {
            return str_repeat($seperator, $node[$depthColumn]) . $node[$column];
        }, $nodes));
    }
}
