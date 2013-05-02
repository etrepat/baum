<?php
namespace Baum\Extensions;

trait ModelExtensions {

  /**
   * Reloads the model from the database.
   *
   * @return \Baum\Node
   */
  public function reload() {
    if ( !$this->exists )
      return $this;

    $dirty = $this->getDirty();
    if ( count($dirty) === 0 )
      return $this;

    $fresh = static::find($this->getKey());

    $this->setRawAttributes($fresh->getAttributes(), true);

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

}
