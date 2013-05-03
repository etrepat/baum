<?php
use Baum\Node;

/**
* {{class}}
*/
class {{class}} extends Node {

  /**
   * Table name.
   *
   * @var string
   */
  protected $table = '{{table}}';

  //////////////////////////////////////////////////////////////////////////////

  //
  // Below come the default values for Baum's own Nested Set implementation
  // column names.
  //
  // You may uncomment and modify the following fields at your own will, provided
  // they match *exactly* those provided in the migration.
  //
  // If you don't plan on modifying any of these you can safely remove them.
  //

  // /**
  // * Column name which stores reference to parent's node.
  // *
  // * @var int
  // */
  // protected $parentColumn = 'parent_id';

  // /**
  // * Column name for the left index.
  // *
  // * @var int
  // */
  // protected $leftColumn = 'lft';

  // /**
  // * Column name for the right index.
  // *
  // * @var int
  // */
  // protected $rightColumn = 'rgt';

  // /**
  // * Column name for the depth field.
  // *
  // * @var int
  // */
  // protected $depthColumn = 'depth';

  // /**
  // * With Baum, all NestedSet-related fields are guarded from mass-assignment
  // * by default.
  // *
  // * @var array
  // */
  // protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

  //////////////////////////////////////////////////////////////////////////////

  //
  // Baum makes available two model events to application developers:
  //
  // 1. `moving`: fired *before* the a node movement operation is performed.
  //
  // 2. `moved`: fired *after* a node movement operation has been performed.
  //
  // In the same way as Eloquent's model events, returning false from the
  // `moving` event handler will halt the operation.
  //
  // Below is a sample `boot` method just for convenience, as an example of how
  // one should hook into those events. This is the *recommended* way to hook
  // into model events, as stated in the documentation. Please refer to the
  // Laravel documentation for details.
  //
  // If you don't plan on using model events in your program you can safely
  // remove all the commented code below.
  //

  // /**
  //  * The "booting" method of the model.
  //  *
  //  * @return void
  //  */
  // protected static function boot() {
  //   // Do not forget this!
  //   parent::boot();

  //   static::moving(function($node) {
  //     // YOUR CODE HERE
  //   });

  //   static::moved(function($node) {
  //     // YOUR CODE HERE
  //   });
  // }

}
