<?php
namespace Baum;

use Illuminate\Database\Eloquent\Model;

/**
 * Node
 *
 * This abstract class implements Nested Set functionality. A Nested Set is a
 * smart way to implement an ordered tree with the added benefit that you can
 * select all of their descendants with a single query. Drawbacks are that
 * insertion or move operations need more complex sql queries.
 *
 * Nested sets are appropiate when you want either an ordered tree (menus,
 * commercial categories, etc.) or an efficient way of querying big trees.
 */
abstract class Node extends Model {

  /**
  * Column name to store the reference to parent's node.
  *
  * @var int
  */
  protected $parentColumn = 'parent_id';

  /**
  * Column name for left index.
  *
  * @var int
  */
  protected $leftColumn = 'lft';

  /**
  * Column name for right index.
  *
  * @var int
  */
  protected $rightColumn = 'rgt';

  /**
  * Column name for depth field.
  *
  * @var int
  */
  protected $depthColumn = 'depth';

  /**
   * Indicates whether we should move to a new parent.
   *
   * @var int
   */
  protected static $moveToNewParentId = FALSE;

  /**
  * Guard NestedSet fields from mass-assignment.
  *
  * @var array
  */
  protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

  /**
  * Get the parent column name.
  *
  * @return string
  */
  public function getParentColumnName() {
    return $this->parentColumn;
  }

  /**
  * Get the table qualified parent column name.
  *
  * @return string
  */
  public function getQualifiedParentColumnName() {
    return $this->getTable(). '.' .$this->getParentColumnName();
  }

  /**
  * Get the value of the models "parent_id" field.
  *
  * @return int
  */
  public function getParentId() {
    return $this->getAttribute($this->getparentColumnName());
  }

  /**
   * Get the "left" field column name.
   *
   * @return string
   */
  public function getLeftColumnName() {
    return $this->leftColumn;
  }

  /**
   * Get the table qualified "left" field column name.
   *
   * @return string
   */
  public function getQualifiedLeftColumName() {
    return $this->getTable() . '.' . $this->getLeftColumnName();
  }

  /**
   * Get the value of the model's "left" field.
   *
   * @return int
   */
  public function getLeft() {
    return $this->getAttribute($this->getLeftColumnName());
  }

  /**
   * Get the "right" field column name.
   *
   * @return string
   */
  public function getRightColumnName() {
    return $this->rightColumn;
  }

  /**
   * Get the table qualified "right" field column name.
   *
   * @return string
   */
  public function getQualifiedRightColumName() {
    return $this->getTable() . '.' . $this->getRightColumnName();
  }

  /**
   * Get the value of the model's "right" field.
   *
   * @return int
   */
  public function getRight() {
    return $this->getAttribute($this->getRightColumnName());
  }

  /**
   * Get the "depth" field column name.
   *
   * @return string
   */
  public function getDepthColumnName() {
    return $this->depthColumn;
  }

  /**
   * Get the table qualified "depth" field column name.
   *
   * @return string
   */
  public function getQualifiedDepthColumnName() {
    return $this->getTable() . '.' . $this->getDepthColumnName();
  }

  /**
   * Get the model's "depth" value.
   *
   * @return int
   */
  public function getDepth() {
    return $this->getAttribute($this->getDepthColumnName());
  }

  /**
  * Parent relation (self-referential) 1-1.
  *
  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
  */
  public function parent() {
    return $this->belongsTo(get_class($this), $this->getParentColumnName());
  }

  /**
  * Children relation (self-referential) 1-N.
  *
  * @return \Illuminate\Database\Eloquent\Relations\HasMany
  */
  public function children() {
    return $this->hasMany(get_class($this), $this->getParentColumnName());
  }

  /**
   * Reloads the model from the database.
   *
   * @return \Baum\Node
   */
  public function reload() {
    $fresh = $this->newQuery()->find($this->getKey());

    $this->setRawAttributes($fresh->getAttributes(), true);

    return $this;
  }

  /**
   * The "booting" method of the model. We'll use this to attach some handlers
   * on model events.
   *
   * @return void
   */
  protected static function boot() {
    parent::boot();

    // On creation, compute default left and right to be at the end of the tree.
    static::creating(function() {
      $this->setDefaultLeftAndRight();
    });
    // static::creating(function($node) {
    //   $highestRightNode = $node->newQuery()->orderBy($node->getRightColumnName(), 'desc')->take(1)->first();

    //   $maxRightValue = 0;
    //   if ( !is_null($highestRightNode) )
    //     $maxRightValue = $highestRightNode->getAttribute($node->getRightColumnName());

    //   // Add the new node to the right of all existing nodes.
    //   $node->setAttribute($node->getLeftColumnName(), $maxRightValue + 1);
    //   $node->setAttribute($node->getRightColumnName(), $maxRightValue + 2);
    // });

    // Before save, check if parent_id changed
    static::saving(function($node) {
      $dirty = $node->getDirty();

      if ( isset($dirty[$node->getParentColumnName()]) )
        $node::$moveToNewParentId = $node->getParentId();
      else
        $node::$moveToNewParentId = FALSE;
    });

    // After save, move to new parent if needed & set depth
    static::saved(function($node) {
      // Move to new parent if needed
      if ( is_null($node::$moveToNewParentId) )
        $node->moveToRoot();
      else if ( $node::$moveToNewParentId !== FALSE )
        $node->moveToChildOf($node::$moveToNewParentId);

      // Update depth
      $level = $node->getLevel();
      $node->newQuery()->where($node->getKeyName(), '=', $node->getKey())->update(array($node->getDepthColumnName() => $level));
      $node->setAttribute($node->getDepthColumnName(), $level);
    });

    // Before delete, prune children
    static::deleting(function($node) {
      if ( !is_null($node->getRight()) && !is_null($node->getLeft()) ) {
        $this->connection->transaction(function() use ($node) {
          $leftColumn   = $node->getLeftColumnName();
          $rightColumn  = $node->getRightColumnName();
          $left         = $node->getLeft();
          $right        = $node->getRight();

          // prune branch off
          $node->newQuery()->where($leftColumn, '>', $left)->where($rightColumn, '<', $right)->delete();

          // update lefts & rights for remaining nodes
          $diff = $right - $left + 1;
          $node->newQuery()->where($leftColumn, '>', $right)->decrement($leftColumn, $diff);
          $node->newQuery()->where($rightColumn, '>', $right)->decrement($rightColumn, $diff);
        });
      }
    });
  }

  /**
   * Returns the first root node.
   *
   * @return NestedSet
   */
  public static function root() {
    return static::roots()->first();
  }

  /**
   * Static query scope. Returns a query scope with all root nodes.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public static function roots() {
    $instance = new static;

    return $instance->newQuery()
                    ->whereNull($instance->getParentColumnName())
                    ->orderBy($instance->getLeftColumnName());
  }

  /**
   * Static query scope. Returns a query scope with all nodes which are at
   * the end of a branch.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public static function allLeaves() {
    $instance = new static;

    return $instance->newQuery()
                    ->whereRaw($instance->getQualifiedRightColumName() . ' - ' . $instance->getQualifiedLeftColumName() . ' = 1')
                    ->orderBy($instance->getLeftColumnName());
  }

  /**
   * Returns true if this is a root node.
   *
   * @return boolean
   */
  public function isRoot() {
    return is_null($this->getParentId());
  }

  /**
   * Returns true if this is a leaf node (end of a branch).
   *
   * @return boolean
   */
  public function isLeaf() {
    return $this->exists && ($this->getRight() - $this->getLeft() == 1);
  }

  /**
   * Returns true if this is a child node.
   *
   * @return boolean
   */
  public function isChild() {
    return !$this->isRoot();
  }

  /**
   * Returns the root node starting at the current node.
   *
   * @return NestedSet
   */
  public function getRoot() {
    if ( $this->exists ) {
      return $this->ancestorsAndSelf()->whereNull($this->getParentColumnName())->first();
    } else {
      $parentId = $this->getParentId();

      if ( !is_null($parentId) && $currentParent = static::find($parentId) ) {
        return $currentParent->getRoot();
      } else {
        return $this;
      }
    }
  }

  /**
   * Instance scope which targes all the ancestor chain nodes including
   * the current one.
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function ancestorsAndSelf() {
    return $this->newQuery()
                ->where($this->getLeftColumnName(), '<=', $this->getLeft())
                ->where($this->getRightColumnName(), '>=', $this->getRight());
  }

  /**
   * Get all the ancestor chain from the database including the current node.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getAncestorsAndSelf($columns = array('*')) {
    return $this->ancestorsAndSelf()->get($columns);
  }

  /**
   * Instance scope which targets all the ancestor chain nodes excluding
   * the current one.
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function ancestors() {
    return $this->withoutSelf($this->ancestorsAndSelf());
  }

  /**
   * Get all the ancestor chain from the database excluding the current node.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getAncestors($columns = array('*')) {
    return $this->ancestors()->get($columns);
  }

  /**
   * Instance scope which targets all children of the parent, including self.
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function siblingsAndSelf() {
    return $this->newQuery()
                ->where($this->getParentColumnName(), $this->getParentId());
  }

  /**
   * Get all children of the parent, including self.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getSiblingsAndSelf($columns = array('*')) {
    return $this->siblingsAndSelf()->get($columns);
  }

  /**
   * Instance scope targeting all children of the parent, except self.
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function siblings() {
    return $this->withoutSelf($this->siblingsAndSelf());
  }

  /**
   * Return all children of the parent, except self.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getSiblings($columns = array('*')) {
    return $this->siblings()->get($columns);
  }

  /**
   * Instance scope targeting all of its nested children which do not have
   * children.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function leaves() {
    return $this->descendants()
                ->whereRaw($this->getQualifiedRightColumName() . ' - ' . $this->getQualifiedLeftColumName() . ' = 1');
  }

  /**
   * Return all of its nested children which do not have children.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getLeaves($columns = array('*')) {
    return $this->leaves()->get($columns);
  }

  /**
   * Scope targeting itself and all of its nested children.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function descendantsAndSelf() {
    return $this->newQuery()
                ->where($this->getLeftColumnName(), '>=', $this->getLeft())
                ->where($this->getLeftColumnName(), '<', $this->getRight());
  }

  /**
   * Retrieve all nested children an self.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getDescendantsAndSelf($columns = array('*')) {
    return $this->descendantsAndSelf()->get($columns);
  }

  /**
   * Set of all children & nested children.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function descendants() {
    return $this->withoutSelf($this->descendantsAndSelf());
  }

  /**
   * Retrieve all of its children & nested children.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getDescendants($columns = array('*')) {
    return $this->descendants()->get($columns);
  }

  /**
  * Returns the level of this node in the tree.
  * Root level is 0.
  *
  * @return int
  */
  public function getLevel() {
    if ( is_null($this->getParentId()) )
      return 0;

    return $this->ancestors()->count();
  }

  /**
   * Returns true if node is a descendant.
   *
   * @param NestedSet
   * @return boolean
   */
  public function isDescendantOf($other) {
    return $this->getLeft() > $other->getLeft() && $this->getLeft() < $other->getRight();
  }

  /**
   * Returns true if node is self or a descendant.
   *
   * @param NestedSet
   * @return boolean
   */
  public function isSelfOrDescendantOf($other) {
   return $this->getLeft() >= $other->getLeft() && $this->getLeft() < $other->getRight();
  }

  /**
   * Returns true if node is an ancestor.
   *
   * @param NestedSet
   * @return boolean
   */
  public function isAncestorOf($other) {
    return $this->getLeft() < $other->getLeft() && $this->getRight() > $other->getLeft();
  }

  /**
   * Returns true if node is self or an ancestor.
   *
   * @param NestedSet
   * @return boolean
   */
  public function isSelfOrAncestorOf($other) {
   return $this->getLeft() <= $other->getLeft() && $this->getRight() > $other->getLeft();
  }

  /**
   * Returns the first sibling to the left.
   *
   * @return NestedSet
   */
  public function getLeftSibling() {
    return $this->siblings()
                ->where($this->getLeftColumnName(), '<', $this->getLeft())
                ->orderBy($this->getLeftColumnName(), 'asc')
                ->first();
  }

  /**
   * Returns the first sibling to the right.
   *
   * @return NestedSet
   */
  public function getRightSibling() {
    return $this->siblings()
                ->where($this->getLeftColumnName(), '>', $this->getLeft())
                ->first();
  }

  /**
   * Find the left sibling and move to left of it.
   *
   * @return Node
   */
  public function moveLeft() {
    return $this->moveToLeftOf($this->getLeftSibling());
  }

  /**
   * Find the right sibling and move to the right of it.
   *
   * @return Node
   */
  public function moveRight() {
    return $this->moveToRightOf($this->getRightSibling());
  }

  /**
   * Move to the node to the left of ...
   *
   * @return Node
   */
  public function moveToLeftOf($node) {
    return $this->moveTo($node, 'left');
  }

  /**
   * Move to the node to the right of ...
   *
   * @return Node
   */
  public function moveToRightOf($node) {
    return $this->moveTo($node, 'right');
  }

  /**
   * Make the node a child of ...
   *
   * @return Node
   */
  public function makeChildOf($node) {
    return $this->moveTo($node, 'child');
  }

  /**
   * Make current node a root node.
   *
   * @return Node
   */
  public function makeRoot() {
    return $this->moveToRight($this->getRoot());
  }

  /**
   * Equals?
   *
   * @param \Baum\Node
   * @return boolean
   */
  public function equals($node) {
    return ($this == $node);
  }

  /**
   * Checks wether the given node is a descendant of itself. Basically, whether
   * its in the subtree defined by the left and right indices.
   *
   * @param \Baum\Node
   * @return boolean
   */
  public function insideSubtree($node) {
    return (
      $node->getLeft()  >= $this->getLeft()   &&
      $node->getLeft()  <= $this->getRight()  &&
      $node->getRight() >= $this->getLeft()   &&
      $node->getRight() <= $this->getRight()
    );
  }

  /**
   * Sets default values for left and right fields.
   *
   * @return void
   */
  protected function setDefaultLeftAndRight() {
    $withHighestRight = $this->newQUery()->orderBy($this->getRightColumnName(), 'desc')->take(1)->first();

    $maxRgt = 0;
    if ( !is_null($withHighestRight) ) $maxRgt = $withHighestRight->getRight();

    $this->setAttribute($this->getLeftColumnName()  , $maxRgt + 1);
    $this->setAttribute($this->getRightColumnName() , $maxRgt + 2);
  }

  /**
   * Return a new QueryBuilder (scope) object without the current node.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  protected function withoutSelf($scope) {
    return $scope->where($this->getKeyName(), '!=', $this->getKey());
  }

  // const VALID_MOVE_POSITIONS = ['child', 'left', 'right', 'root'];
  const VALID_MOVE_POSITIONS = ['child', 'left', 'right'];

  /**
   * Main move method. Here we handle all node movements with the corresponding
   * lft/rgt index updates.
   *
   * TODO: reduce/split/extract/refactor/whatever this monstrosity...
   *
   * @param Baum\Node $target
   * @param int       $position
   * @return \Baum\Node
   */
  protected function moveTo($target, $position) {
    if ( !$this->exists )
      throw new MoveNotPossibleException('A new node cannot be moved.');

    if ( array_search($position, VALID_MOVE_POSITIONS) === FALSE )
      throw new MoveNotPossibleException("Position should be one of {self::VALID_MOVE_POSITIONS} but is $position.");

    if ( $this->equals($target) )
      throw new MoveNotPossibleException('A node cannot be moved to itself.');

    if ( $this->insideSubtree($target) )
      throw new MoveNotPossibleException('A node cannot be moved to a descendant of itself (inside moved tree).');

    $this->getConnection()->transaction(function($connection) use ($target, $position) {
      $bound = 1;
      switch ($position) {
        case 'child':
          $bound = $target->getRight();
          break;

        case 'left':
          $bound = $target->getLeft();
          break;

        case 'right':
          $bound = $target->getRight() + 1;
          break;
      }
      $bound = $bound > $this->getRight() ? $bound - 1 : $bound;

      $otherBound = $bound > $this->getRight() ? $this->getRight() + 1 : $this->getleft() - 1;

      // return early if there's no change to be made
      if ( $bound == $this->getRight() || $bound == $this->getLeft() )
        return 0;

      // we have defined the boundaries of two non-overlapping intervals,
      // so sorting puts both the intervals and their boundaries in order
      $boundaries = array($this->getLeft(), $this->getRight(), $bound, $otherBound);
      sort($boundaries);
      list($a, $b, $c, $d) = $boundaries;

      $newParent = $target->getParentId();
      if ( $position == 'child' )
        $newParent = $target->id;

      // Update
      $builder  = $this->newQuery();
      $query    = $builder->getQuery();
      $grammar  = $query->getGrammar();

      $currentId      = $this->id;
      $leftColumn     = $this->getLeftColumnName();
      $rightColumn    = $this->getRightColumnName();
      $parentColumn   = $this->getParentColumnName();
      $wrappedLeft    = $grammar->wrap($leftColumn);
      $wrappedRight   = $grammar->wrap($rightColumn);
      $wrappedParent  = $grammar->wrap($parentColumn);
      $wrappedId      = $grammar->wrap($this->getKeyName());

      $lftSql = "CASE
        WHEN $wrappedLeft BETWEEN $a AND $b THEN $wrappedLeft + $d - $b
        WHEN $wrappedLeft BETWEEN $c AND $d THEN $wrappedLeft + $a - $c
        ELSE $wrappedLeft END";

      $rgtSql = "CASE
        WHEN $wrappedRight BETWEEN $a AND $b THEN $wrappedRight + $d - $b
        WHEN $wrappedRight BETWEEN $c AND $d THEN $wrappedRight + $a - $c
        ELSE $wrappedRight END";

      $parentSql = "CASE
        WHEN $wrappedId = $currentId THEN $newParent
        ELSE $wrappedParent END";

      return $builder->whereBetween($leftColumn, [$a, $d])
              ->orWhereBetween($rightColumn, [$a, $d])
              ->update([
                  $leftColumn   => $connection->raw($lftSql),
                  $rightColumn  => $connection->raw($rgtSql),
                  $parentColumn => $connection->raw($parentSql)
                ]);
    });

    $target->reload();

    // TODO:
    // 1. set depth

    foreach ($this->getDescendants() as $descendant) {
      $descendant->save();
    }

    return $this->reload();
  }

  // -- DEBUG

  // for debugging purposes only...
  public function toText() {
    $text = [];

    foreach($this->getDescendantsAndSelf() as $node) {
      $nesting  = str_repeat('*', $node->getLevel());
      $parentId = is_null($node->getParentId()) ? 'NULL' : $node->getParentId();

      $text[] = "$nesting {$node->getKey()} (pid:$parentId, lft:{$node->getLeft()}, rgt:{$node->getRight()}, dpth:{$node->getDepth()})";
    }

    return implode("\n", $text);
  }
}
