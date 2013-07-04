<?php

namespace Baum\Extensions\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel {

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
