<?php

namespace Baum\Extensions\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;

class Builder extends BaseBuilder {

  /**
   * Replace the "order by" clause of the current query.
   *
   * @param  string  $column
   * @param  string  $direction
   * @return \Illuminate\Database\Query\Builder|static
   */
  public function reOrderBy($column, $direction = 'asc') {
    $this->orders = null;

    if ( !is_null($column) ) return $this->orderBy($column, $direction);

    return $this;
  }

  /**
   * Execute an aggregate function on the database.
   *
   * @param  string  $function
   * @param  array   $columns
   * @return mixed
   */
  public function aggregate($function, $columns = array('*')) {
    // Postgres doesn't like ORDER BY when there's no GROUP BY clause
    if ( !isset($this->groups) )
      $this->reOrderBy(null);

    return parent::aggregate($function, $columns);
  }

}
