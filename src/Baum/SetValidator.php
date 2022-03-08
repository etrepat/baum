<?php
namespace Baum;

use Illuminate\Database\Capsule\Manager as Capsule;

class SetValidator {

  /**
  * Node instance for reference
  *
  * @var \Baum\Node
  */
  protected $node = NULL;

  /**
   * Create a new \Baum\SetValidator class instance.
   *
   * @param   \Baum\Node      $node
   * @return  void
   */
  public function __construct($node) {
    $this->node = $node;
  }

  /**
   * Determine if the validation passes.
   *
   * @return boolean
   */
  public function passes() {
    return $this->validateBounds() && $this->validateDuplicates() &&
      $this->validateRoots();
  }

  /**
   * Determine if validation fails.
   *
   * @return boolean
   */
  public function fails() {
    return !$this->passes();
  }

  /**
   * Validates bounds of the nested tree structure. It will perform checks on
   * the `lft`, `rgt` and `parent_id` columns. Mainly that they're not null,
   * rights greater than lefts, and that they're within the bounds of the parent.
   *
   * @return boolean
   */
  protected function validateBounds() {
    $connection = $this->node->getConnection();
    $grammar    = $connection->getQueryGrammar();

    $tableName      = $this->node->getTable();
    $primaryKeyName = $this->node->getKeyName();
    $parentColumn   = $this->node->getQualifiedParentColumnName();

    $lftCol = $grammar->wrap($this->node->getLeftColumnName());
    $rgtCol = $grammar->wrap($this->node->getRightColumnName());

    $qualifiedLftCol    = $grammar->wrap($this->node->getQualifiedLeftColumnName());
    $qualifiedRgtCol    = $grammar->wrap($this->node->getQualifiedRightColumnName());
    $qualifiedParentCol = $grammar->wrap($this->node->getQualifiedParentColumnName());

    $whereStm = "($qualifiedLftCol IS NULL OR
      $qualifiedRgtCol IS NULL OR
      $qualifiedLftCol >= $qualifiedRgtCol OR
      ($qualifiedParentCol IS NOT NULL AND
        ($qualifiedLftCol <= parent.$lftCol OR
          $qualifiedRgtCol >= parent.$rgtCol)))";

    $query = $this->node->newQuery()
      ->join($connection->raw($grammar->wrapTable($tableName).' AS parent'),
             $parentColumn, '=', $connection->raw('parent.'.$grammar->wrap($primaryKeyName)),
             'left outer')
      ->whereRaw($whereStm);

    return ($query->count() == 0);
  }

  /**
   * Checks that there are no duplicates for the `lft` and `rgt` columns.
   *
   * @return boolean
   */
  protected function validateDuplicates() {
    return (
      !$this->duplicatesExistForColumn($this->node->getQualifiedLeftColumnName()) &&
      !$this->duplicatesExistForColumn($this->node->getQualifiedRightColumnName())
    );
  }

  /**
   * For each root of the whole nested set tree structure, checks that their
   * `lft` and `rgt` bounds are properly set.
   *
   * @return boolean
   */
  protected function validateRoots() {
    $roots = forward_static_call(array(get_class($this->node), 'roots'))->get();

    // If a scope is defined in the model we should check that the roots are
    // valid *for each* value in the scope columns.
    if ( $this->node->isScoped() )
      return $this->validateRootsByScope($roots);

    return $this->isEachRootValid($roots);
  }

  /**
   * Checks if duplicate values for the column specified exist. Takes
   * the Nested Set scope columns into account (if appropiate).
   *
   * @param   string  $column
   * @return  boolean
   */
  protected function duplicatesExistForColumn($column) {
    $connection = $this->node->getConnection();
    $grammar    = $connection->getQueryGrammar();

    $columns = array_merge($this->node->getQualifiedScopedColumns(), array($column));

    $columnsForSelect = implode(', ', array_map(function($col) use ($grammar) {
      return $grammar->wrap($col); }, $columns));

    $wrappedColumn = $grammar->wrap($column);

    $query = $this->node->newQuery()
      ->select($connection->raw("$columnsForSelect, COUNT($wrappedColumn)"))
      ->havingRaw("COUNT($wrappedColumn) > 1");

    foreach($columns as $col)
      $query->groupBy($col);

    $result = $query->first();

    return !is_null($result);
  }

  /**
   * Check that each root node in the list supplied satisfies that its bounds
   * values (lft, rgt indexes) are less than the next.
   *
   * @param   mixed   $roots
   * @return  boolean
   */
  protected function isEachRootValid($roots) {
    $left = $right = 0;

    foreach($roots as $root) {
      $rootLeft   = $root->getLeft();
      $rootRight  = $root->getRight();

      if ( !($rootLeft > $left && $rootRight > $right) )
        return false;

      $left   = $rootLeft;
      $right  = $rootRight;
    }

    return true;
  }

  /**
   * Check that each root node in the list supplied satisfies that its bounds
   * values (lft, rgt indexes) are less than the next *within each scope*.
   *
   * @param   mixed   $roots
   * @return  boolean
   */
  protected function validateRootsByScope($roots) {
    foreach($this->groupRootsByScope($roots) as $scope => $groupedRoots) {
      $valid = $this->isEachRootValid($groupedRoots);

      if ( !$valid )
        return false;
    }

    return true;
  }

  /**
   * Given a list of root nodes, it returns an array in which the keys are the
   * array of the actual scope column values and the values are the root nodes
   * inside that scope themselves
   *
   * @param   mixed   $roots
   * @return  array
   */
  protected function groupRootsByScope($roots) {
    $rootsGroupedByScope = array();

    foreach($roots as $root) {
      $key = $this->keyForScope($root);

      if ( !isset($rootsGroupedByScope[$key]) )
        $rootsGroupedByScope[$key] = array();

      $rootsGroupedByScope[$key][] = $root;
    }

    return $rootsGroupedByScope;
  }

  /**
   * Builds a single string for the given scope columns values. Useful for
   * making array keys for grouping.
   *
   * @param Baum\Node   $node
   * @return string
   */
  protected function keyForScope($node) {
    return implode('-', array_map(function($column) use ($node) {
      $value = $node->getAttribute($column);

      if ( is_null($value) )
        return 'NULL';

      return $value;
    }, $node->getScopedColumns()));
  }

}
