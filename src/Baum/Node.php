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
  protected static $moveToNewParentId = NULL;

  /**
  * Guard NestedSet fields from mass-assignment.
  *
  * @var array
  */
  protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

  /**
   * Create a new Node model instance.
   *
   * @param  array  $attributes
   * @return void
   */
  public function __construct(array $attributes = array()) {
    parent::__construct($attributes);

    $this->registerEventListeners();
  }

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
   * @return \Baum\Node
   */
  public function moveLeft() {
    return $this->moveToLeftOf($this->getLeftSibling());
  }

  /**
   * Find the right sibling and move to the right of it.
   *
   * @return \Baum\Node
   */
  public function moveRight() {
    return $this->moveToRightOf($this->getRightSibling());
  }

  /**
   * Move to the node to the left of ...
   *
   * @return \Baum\Node
   */
  public function moveToLeftOf($node) {
    return $this->moveTo($node, 'left');
  }

  /**
   * Move to the node to the right of ...
   *
   * @return \Baum\Node
   */
  public function moveToRightOf($node) {
    return $this->moveTo($node, 'right');
  }

  /**
   * Make the node a child of ...
   *
   * @return \Baum\Node
   */
  public function makeChildOf($node) {
    return $this->moveTo($node, 'child');
  }

  /**
   * Make current node a root node.
   *
   * @return \Baum\Node
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
   * Registers event listeners on a Node instance.
   *
   * 1. "creating": Before creating a new Node we'll assign a default value for
   * the left and right indexes.
   *
   * 2. "saving": Before saving, we'll perform a check to see if we have to move
   * to another parent.
   *
   * 3. "saved": Move to the new parent after saving if needed and re-set depth.
   *
   * 4. "deleting": Before delete we should prune all children and update
   * the left and right indexes for the remaining nodes.
   *
   * @return void
   */
  protected function registerEventListeners() {
    static::creating(function() {
      $this->setDefaultLeftAndRight();
    });

    static::saving(function() {
      $this->storeNewParent();
    });

    static::saved(function() {
      $this->moveToNewParent();
      $this->setDepth();
    });

    static::deleting(function() {
      $this->destroyDescendants();
    });
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
   * Store the parent_id if the attribute is modified so as we are able to move
   * the node to this new parent after saving.
   *
   * @return void
   */
  protected function storeNewParent() {
    $dirty = $this->getDirty();

    if ( isset($dirty[$this->getParentColumnName()]) )
      static::$moveToNewParentId = $this->getParentId();
    else
      static::$moveToNewParentId = FALSE;
  }

  /**
   * Move to the new parent if appropiate.
   *
   * @return void
   */
  protected function moveToNewParent() {
    $pid = static::$moveToNewParentId;

    if ( is_null($pid) )
      $this->makeRoot();
    else if ( $pid !== FALSE )
      $this->moveToChildOf($pid);
  }

  /**
   * Sets the depth attribute
   *
   * @return \Baum\Node
   */
  public function setDepth() {
    $this->getConnection()->transaction(function() {
      $this->reload();

      $level = $this->getLevel();

      $this->newQuery()->where($this->getKeyName(), '=', $this->getKey())->update(array($this->getDepthColumnName() => $level));
      $this->setAttribute($this->getDepthColumnName(), $level);
    });

    return $this;
  }

  /**
   * Prunes a branch off the tree, shifting all the elements on the right
   * back to the left so the counts work.
   *
   * @return void;
   */
  protected function destroyDescendants() {
    if ( is_null($this->getRight()) || is_null($this->getLeft()) ) return;

    $this->getConnection()->transaction(function() {
      $this->reload();

      $lftCol = $this->getLeftColumnName();
      $rgtCol = $this->getRightColumnName();
      $lft    = $this->getLeft();
      $rgt    = $this->getRight();

      // Prune children
      $this->newQuery()->where($lftCol, '>', $lft)->where($rgtCol, '<', $rgt)->delete();

      // Update left and right indexes for the remaining nodes
      $diff = $rgt - $lft + 1;

      $this->newQuery()->where($lftCol, '>', $rgt)->decrement($lftCol, $diff);
      $this->newQuery()->where($rgtCol, '>', $rgt)->decrement($rgtCol, $diff);
    });
  }

  /**
   * Return a new QueryBuilder (scope) object without the current node.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  protected function withoutSelf($scope) {
    return $scope->where($this->getKeyName(), '!=', $this->getKey());
  }

  /**
   * Main move method. Here we handle all node movements with the corresponding
   * lft/rgt index updates.
   *
   * @param Baum\Node|int $target
   * @param string        $position
   * @return \Baum\Node
   */
  protected function moveTo($target, $position) {
    return Move::to($this, $target, $position);
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
