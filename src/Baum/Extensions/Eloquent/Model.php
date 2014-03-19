<?php

namespace Baum\Extensions\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Baum\Extensions\Query\Builder as QueryBuilder;

abstract class Model extends BaseModel {

  /**
   * Reloads the model from the database.
   *
   * @return \Baum\Node
   *
   * @throws ModelNotFoundException
   */
  public function reload() {
    if ( $this->exists || ($this->areSoftDeletesEnabled() && $this->trashed()) ) {
      $fresh = $this->getFreshInstance();

      if ( is_null($fresh) )
        throw with(new ModelNotFoundException)->setModel(get_called_class());

      $this->setRawAttributes($fresh->getAttributes(), true);

      $this->setRelations($fresh->getRelations());

      $this->exists = $fresh->exists;
    } else {
      // Revert changes if model is not persisted
      $this->attributes = $this->original;
    }

    return $this;
  }

  /**
   * Get the observable event names.
   *
   * @return array
   */
  public function getObservableEvents() {
    return array_merge(array('moving', 'moved'), parent::getObservableEvents());
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

  /**
   * Get a new query builder instance for the connection.
   *
   * @return \Baum\Extensions\Query\Builder
   */
  protected function newBaseQueryBuilder() {
    $conn = $this->getConnection();

    $grammar = $conn->getQueryGrammar();

    return new QueryBuilder($conn, $grammar, $conn->getPostProcessor());
  }

  /**
   * Returns a fresh instance from the database.
   *
   * @return \Baum\Node
   */
  protected function getFreshInstance() {
    if ( $this->areSoftDeletesEnabled() )
      return static::withTrashed()->find($this->getKey());

    return static::find($this->getKey());
  }

  /**
   * Returns wether soft delete functionality is enabled on the model or not.
   *
   * @return boolean
   */
  public function areSoftDeletesEnabled() {
    // Soft-delete functionality in 4.2 has been moved into a trait.
    // The proper way to check if a model includes a global scope in >= 4.2
    // should look similar to the following:
    //
    //    static::hasGlobalScope(new SoftDeletingScope);
    //
    // We are doing it this way (not the best probably) to keep backwards
    // compatibility...
    return (
      (property_exists($this, 'softDelete') && $this->softDelete == true) ||
      (!property_exists($this, 'softDelete') && method_exists($this, 'getDeletedAtColumn'))
    );
  }

  /**
   * Static method which returns wether soft delete functionality is enabled
   * on the model.
   *
   * @return boolean
   */
  public static function softDeletesEnabled() {
    return with(new static)->areSoftDeletesEnabled();
  }

}
