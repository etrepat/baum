<?php

namespace Baum\NestedSet\Concerns;

use Baum\NestedSet\NodeObserver;

trait HasEvents
{
    /**
     * Boot the HasEvents trait for a model.
     *
     * @return void
     */
    public static function bootHasEvents()
    {
        static::observe(new NodeObserver);
    }

    /**
     * Initialize the HasEvents trait for an instance.
     *
     * @return void
     */
    public function initializeHasEvents()
    {
        $this->addObservableEvents('moving', 'moved');
    }

    /**
     * Register a moving model event with the dispatcher.
     *
     * @param  Closure|string  $callback
     * @return void
     */
    public static function moving($callback)
    {
        static::registerModelEvent('moving', $callback);
    }

    /**
     * Register a moved model event with the dispatcher.
     *
     * @param  Closure|string  $callback
     * @return void
     */
    public static function moved($callback)
    {
        static::registerModelEvent('moved', $callback);
    }
}
