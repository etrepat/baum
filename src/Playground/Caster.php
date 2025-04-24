<?php

namespace Baum\Playground;

use Symfony\Component\VarDumper\Caster\Caster as BaseCaster;

class Caster
{
    /**
     * Available casters.
     *
     * @var array
     */
    protected static $availableCasters = [
        'Illuminate\Support\Collection' => 'Baum\Playground\Caster::castCollection',
        'Illuminate\Database\Eloquent\Model' => 'Baum\Playground\Caster::castModel',
        'Illuminate\Database\Eloquent\Builder' => 'Baum\Playground\Caster::castEloquentBuilder'
    ];

    /**
     * Available casters accessor.
     *
     * @return array
     */
    public static function availableCasters()
    {
        return static::$availableCasters;
    }

    /**
     * Get an array representing the properties of a collection.
     *
     * @param  \Illuminate\Support\Collection  $collection
     * @return array
     */
    public static function castCollection($collection)
    {
        return [
            BaseCaster::PREFIX_VIRTUAL.'all' => $collection->all(),
        ];
    }

    /**
     * Get an array representing the properties of a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    public static function castModel($model)
    {
        $attributes = array_merge($model->getAttributes(), $model->getRelations());

        $visible = array_flip(
            $model->getVisible() ?: array_diff(array_keys($attributes), $model->getHidden())
        );

        $results = [];

        foreach (array_intersect_key($attributes, $visible) as $key => $value) {
            $results[(isset($visible[$key]) ? BaseCaster::PREFIX_VIRTUAL : BaseCaster::PREFIX_PROTECTED).$key] = $value;
        }

        return $results;
    }

    /**
     * Get the raw query string for a builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return array
     */
    public static function castEloquentBuilder($builder)
    {
        $sql = $builder->toSql();

        $bindings = $builder->getBindings();

        return [
            BaseCaster::PREFIX_VIRTUAL.'sql' => vsprintf(
                str_replace('?', '%s', $sql),
                array_map(function ($v) {
                    return is_int($v) || is_float($v) ? (string) $v : "'{$v}'";
                }, $bindings)
            ),
        ];
    }
}
