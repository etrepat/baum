<?php

namespace Baum\NestedSet\Concerns;

trait HasDepth {
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
}
