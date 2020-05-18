<?php

namespace Baum\NestedSet\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderingScope implements Scope
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
        $builder->orderBy($model->getQualifiedOrderColumnName());
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $this->addReOrderBy($builder);
    }

    /**
     * Add a 'reOrderBy' macro to easily reset or restart an order by sequence.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function addReOrderBy(Builder $builder)
    {
        $builder->macro('reOrderBy', function ($builder, $column = null, $direction = 'asc') {
            $query = $builder->getQuery();

            $query->{$query->unions ? 'unionOrders' : 'orders'} = null;

            if (!is_null($column)) {
                $query->orderBy($column, $direction);
            }

            $builder->setQuery($query);

            return $builder->withoutGlobalScope($this);
        });
    }
}
