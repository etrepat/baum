<?php

namespace Baum\NestedSet\Concerns;

use Baum\NestedSet\Move;

trait Movable
{
    /**
     * Indicates whether we should move to a new parent.
     *
     * @var int
     */
    protected static $moveToNewParentId = null;

    /**
     * Main move method. Here we handle all node movements with the corresponding
     * lft/rgt index updates.
     *
     * @param Baum\Node|int $target
     * @param string        $position
     * @return \Baum\Node
     */
    protected function moveTo($target, $position)
    {
        return Move::to($this, $target, $position);
    }

    /**
     * Find the left sibling and move to left of it.
     *
     * @return \Baum\Node
     */
    public function moveLeft()
    {
        return $this->moveToLeftOf($this->getLeftSibling());
    }

    /**
     * Find the right sibling and move to the right of it.
     *
     * @return \Baum\Node
     */
    public function moveRight()
    {
        return $this->moveToRightOf($this->getRightSibling());
    }

    /**
     * Move to the node to the left of ...
     *
     * @return \Baum\Node
     */
    public function moveToLeftOf($node)
    {
        return $this->moveTo($node, 'left');
    }

    /**
     * Move to the node to the right of ...
     *
     * @return \Baum\Node
     */
    public function moveToRightOf($node)
    {
        return $this->moveTo($node, 'right');
    }

    /**
     * Alias for moveToRightOf
     *
     * @return \Baum\Node
     */
    public function makeNextSiblingOf($node)
    {
        return $this->moveToRightOf($node);
    }

    /**
     * Alias for moveToRightOf
     *
     * @return \Baum\Node
     */
    public function makeSiblingOf($node)
    {
        return $this->moveToRightOf($node);
    }

    /**
     * Alias for moveToLeftOf
     *
     * @return \Baum\Node
     */
    public function makePreviousSiblingOf($node)
    {
        return $this->moveToLeftOf($node);
    }

    /**
     * Make the node a child of ...
     *
     * @return \Baum\Node
     */
    public function makeChildOf($node)
    {
        return $this->moveTo($node, 'child');
    }

    /**
     * Make the node the first child of ...
     *
     * @return \Baum\Node
     */
    public function makeFirstChildOf($node)
    {
        if ($node->children()->count() == 0) {
            return $this->makeChildOf($node);
        }

        return $this->moveToLeftOf($node->children()->first());
    }

    /**
     * Make the node the last child of ...
     *
     * @return \Baum\Node
     */
    public function makeLastChildOf($node)
    {
        return $this->makeChildOf($node);
    }

    /**
     * Make current node a root node.
     *
     * @return \Baum\Node
     */
    public function makeRoot()
    {
        return $this->moveTo($this, 'root');
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
}
