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
        Concerns\HasDepth,
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
