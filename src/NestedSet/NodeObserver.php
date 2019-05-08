<?php

namespace Baum\NestedSet;

class NodeObserver
{
    /**
     * Executed *before* creating a new nested set node. Ensures the left
     * and right have a proper default value.
     *
     * @param mixed $node
     * @return void
     */
    public function creating($node)
    {
        $node->setDefaultLeftAndRight();
    }

    /**
     * Executed *before* saving a nested set node instance. It checks if we
     * have to perform a move operation to another parent.
     *
     * @param mixed $node
     * @return void
     */
    public function saving($node)
    {
        $node->storeNewParent();
    }

    /**
     * Executed *after* saving a nested set node instance. It will move to
     * a new parent (if needed) and recomputes the depth attribute.
     *
     * @param mixed $node
     * @return void
     */
    public function saved($node)
    {
        $node->moveToNewParent();
        $node->setDepth();
    }

    /**
     * Executed *before* deleting a nested set node instance. Will delete
     * all of the node's descendants.
     *
     * @param mixed $node
     * @return void
     */
    public function deleting($node)
    {
        $node->destroyDescendants();
    }

    /**
     * Executed *before* a soft-delete restore operation. Shifts its siblings.
     *
     * @param mixed $node
     * @return void
     */
    public function restoring($node)
    {
        $node->shiftSiblingsForRestore();
    }

    /**
     * *After* having restored a soft-deleted nested set node instance, restore
     * all of its descendants.
     *
     * @param mixed $node
     * @return void
     */
    public function restored($node)
    {
        $node->restoreDescendants();
    }
}
