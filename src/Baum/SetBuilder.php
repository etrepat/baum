<?php
namespace Baum;

use Baum\Helpers\DatabaseHelper as DB;

class SetBuilder {

  /**
  * Node instance for reference
  *
  * @var \Baum\Node
  */
  protected $node = NULL;

  /**
   * Create a new \Baum\SetBuilder class instance.
   *
   * @param   \Baum\Node      $node
   * @return  void
   */
  public function __construct($node) {
    $this->node = $node;
  }

  public function rebuild() {
    $alreadyValid = forward_static_call(array(get_class($this->node), 'isValid'));

    // Do not rebuild a valid Nested Set tree structure
    if ( $alreadyValid )
      return true;

    foreach($this->roots() as $rootNode) {
      // TODO:
      //  Rebuild lefts and rights for each root node and its children (recursively).
      //  We should set left (and keep track of the current left bound), then
      //  search for each children and recursively set the left index. When backtracking
      //  (going back up the recursive chain) we should set the rights (while keeping
      //  track of the last right index used)...
    }
  }

  /**
   * Returns all root nodes for the current database table with a sorting
   * order suitable for rebuilding the Nested Set tree structure: lft, rgt, id
   *
   * @return Illuminate\Database\Eloquent\Collection
   */
  protected function roots() {
    return $this->node->newQuery()
      ->whereNull($this->node->getQualifiedParentColumnName())
      ->orderBy($this->node->getQualifiedLeftColumnName())
      ->orderBy($this->node->getQualifiedRightColumnName())
      ->orderBy($this->node->getKey())
      ->get();
  }

}
