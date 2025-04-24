<?php

namespace Baum\NestedSet\Concerns;

use Illuminate\Database\Eloquent\SoftDeletingScope;

trait WorksWithSoftDeletes
{
    /**
     * Returns wether soft delete functionality is enabled on the current model
     * instance or not.
     *
     * @return boolean
     */
    public function hasSoftDeletes()
    {
        // To determine if there's a global soft delete scope defined we must
        // first determine if there are any, to workaround a non-existent key error.
        $globalScopes = $this->getGlobalScopes();

        if (count($globalScopes) === 0) {
            return false;
        }

        // Now that we're sure that the calling class has some kind of global scope
        // we check for the SoftDeletingScope existance
        return static::hasGlobalScope(new SoftDeletingScope);
    }

    /**
     * "Makes room" for the the current node between its siblings.
     *
     * @return void
     */
    public function shiftSiblingsForRestore()
    {
        if (!$this->hasSoftDeletes()) {
            return;
        }

        if (is_null($this->getRight()) || is_null($this->getLeft())) {
            return;
        }

        $this->getConnection()->transaction(function () {
            $lftCol = $this->getLeftColumnName();
            $rgtCol = $this->getRightColumnName();
            $lft    = $this->getLeft();
            $rgt    = $this->getRight();

            $diff = $rgt - $lft + 1;

            $this->newQuery()->where($lftCol, '>=', $lft)->increment($lftCol, $diff);
            $this->newQuery()->where($rgtCol, '>=', $lft)->increment($rgtCol, $diff);
        });
    }

    /**
     * Restores all of the current node's descendants.
     *
     * @return void
     */
    public function restoreDescendants()
    {
        if (!$this->hasSoftDeletes()) {
            return;
        }

        if (is_null($this->getRight()) || is_null($this->getLeft())) {
            return;
        }

        $this->getConnection()->transaction(function () {
            $this->newQuery()
            ->withTrashed()
            ->where($this->getLeftColumnName(), '>', $this->getLeft())
            ->where($this->getRightColumnName(), '<', $this->getRight())
            ->update([
                $this->getDeletedAtColumn() => null,
                $this->getUpdatedAtColumn() => $this->{$this->getUpdatedAtColumn()}
            ]);
        });
    }
}
