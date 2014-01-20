<?php
namespace Baum\Helpers;

use \Closure;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseHelper {

  /**
   * Get a new raw query expression (helper for calling Connection->raw)
   *
   * @param  mixed  $value
   * @return \Illuminate\Database\Query\Expression
   */
  public static function raw($value) {
    return Capsule::connection()->raw($value);
  }

  /**
   * Wrap a value in keyword identifiers (helper for calling Grammar->wrap)
   *
   * @param  string  $value
   * @return string
   */
  public static function wrap($value) {
    return Capsule::connection()->getQueryGrammar()->wrap($value);
  }

  /**
   * Execute a Closure within a transaction
   * (helper for calling Connection->transaction)
   *
   * @param  Closure  $callback
   * @return mixed
   *
   * @throws \Exception
   */
  public static function transaction(Closure $callback) {
    return Capsule::connection()->transaction($callback);
  }

}
