<?php
namespace Baum\Extensions;

trait ModelExtensions {

  /**
   * Reloads the model from the database.
   *
   * @return \Baum\Node
   */
  public function reload() {
    if ( !$this->exists ) {
      $this->syncOriginal();
    } else {
      $fresh = static::find($this->getKey());

      $this->setRawAttributes($fresh->getAttributes(), true);
    }

    return $this;
  }

  /**
   * Find first model.
   *
   * @return \Illuminate\Database\Eloquent\Model
   */
  public static function first() {
    $instance = new static;

    return $instance->newQuery()->orderBy($instance->getKeyName(), 'asc')->first();
  }

  /**
   * Find last model.
   *
   * @return \Illuminate\Database\Eloquent\Model
   */
  public static function last() {
    $instance = new static;

    return $instance->newQuery()->orderBy($instance->getKeyName(), 'desc')->first();
  }

  /**
   * Register a moving model event with the dispatcher.
   *
   * @param  Closure|string  $callback
   * @return void
   */
  public static function moving($callback) {
    static::registerModelEvent('moving', $callback);
  }

  /**
   * Register a moved model event with the dispatcher.
   *
   * @param  Closure|string  $callback
   * @return void
   */
  public static function moved($callback) {
    static::registerModelEvent('moved', $callback);
  }

}
