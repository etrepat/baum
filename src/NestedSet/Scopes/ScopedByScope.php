<?php

namespace Baum\NestedSet\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScopedByScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($model->isScoped() && $model->exists) {
            foreach ($model->getScopedColumnNames() as $fld) {
                $builder->where($model->qualifyColumn($fld), '=', $model->getAttribute($fld));
            }
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $this->addUnscoped($builder);

        $this->addScopedBy($builder);
    }

    /**
     * 'unscoped' macro.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function addUnscoped(Builder $builder)
    {
        $builder->macro('unscoped', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * 'scopedBy' macro.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function addScopedBy(Builder $builder)
    {
        $builder->macro('scopedBy', function (Builder $builder, $scopedBy = []) {
            $model = $builder->getModel();

            $scopeColumns = array_merge($scopedBy, $model->isScoped() ? $model->getScopedColumnNames() : []);

            return array_reduce($scopeColumns, function ($builder, $column) use ($model) {
                return $builder->where(
                    $model->qualifyColumn($column),
                    $model->getAttribute($column)
                );
            }, $builder->unscoped());
        });
    }
}
