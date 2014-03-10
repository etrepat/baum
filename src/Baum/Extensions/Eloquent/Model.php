<?php

namespace Baum\Extensions\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Baum\Extensions\Query\Builder as QueryBuilder;

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

}
