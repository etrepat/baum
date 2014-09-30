<?php
namespace Baum;

use Baum\Extensions\Eloquent\Collection;
use Baum\Extensions\Eloquent\Model;

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
   * @var string
   */
  protected $parentColumn = 'parent_id';

  /**
   * Column name for left index.
   *
   * @var string
   */
  protected $leftColumn = 'lft';

  /**
   * Column name for right index.
   *
   * @var string
   */
  protected $rightColumn = 'rgt';

  /**
   * Column name for depth field.
   *
   * @var string
   */
  protected $depthColumn = 'depth';

  /**
   * Column to perform the default sorting
   *
   * @var string
   */
  protected $orderColumn = null;

  /**
  * Guard NestedSet fields from mass-assignment.
  *
  * @var array
  */
  protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

  /**
   * Indicates whether we should move to a new parent.
   *
   * @var int
   */
  protected static $moveToNewParentId = NULL;

  /**
   * Columns which restrict what we consider our Nested Set list
   *
   * @var array
   */
  protected $scoped = array();

  /**
   * The "booting" method of the model.
   *
   * We'll use this method to register event listeners on a Node instance as
   * suggested in the beta documentation...
   *
   * TODO:
   *
   *    - Find a way to avoid needing to declare the called methods "public"
   *    as registering the event listeners *inside* this methods does not give
   *    us an object context.
   *
   * Events:
   *
   *    1. "creating": Before creating a new Node we'll assign a default value
   *    for the left and right indexes.
   *
   *    2. "saving": Before saving, we'll perform a check to see if we have to
   *    move to another parent.
   *
   *    3. "saved": Move to the new parent after saving if needed and re-set
   *    depth.
   *
   *    4. "deleting": Before delete we should prune all children and update
   *    the left and right indexes for the remaining nodes.
   *
   *    5. (optional) "restoring": Before a soft-delete node restore operation,
   *    shift its siblings.
   *
   *    6. (optional) "restore": After having restored a soft-deleted node,
   *    restore all of its descendants.
   *
   * @return void
   */
  protected static function boot() {
    parent::boot();

    static::creating(function($node) {
      $node->setDefaultLeftAndRight();
    });

    static::saving(function($node) {
      $node->storeNewParent();
    });

    static::saved(function($node) {
      $node->moveToNewParent();
      $node->setDepth();
    });

    static::deleting(function($node) {
      $node->destroyDescendants();
    });

    if ( static::softDeletesEnabled() ) {
      static::restoring(function($node) {
        $node->shiftSiblingsForRestore();
      });

      static::restored(function($node) {
        $node->restoreDescendants();
      });
    }
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
  public function getQualifiedLeftColumnName() {
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
  public function getQualifiedRightColumnName() {
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
   * Get the "order" field column name.
   *
   * @return string
   */
  public function getOrderColumnName() {
    return is_null($this->orderColumn) ? $this->getLeftColumnName() : $this->orderColumn;
  }

  /**
   * Get the table qualified "order" field column name.
   *
   * @return string
   */
  public function getQualifiedOrderColumnName() {
    return $this->getTable() . '.' . $this->getOrderColumnName();
  }

  /**
   * Get the model's "order" value.
   *
   * @return mixed
   */
  public function getOrder() {
    return $this->getAttribute($this->getOrderColumnName());
  }

  /**
   * Get the column names which define our scope
   *
   * @return array
   */
  public function getScopedColumns() {
    return (array) $this->scoped;
  }

  /**
   * Get the qualified column names which define our scope
   *
   * @return array
   */
  public function getQualifiedScopedColumns() {
    if ( !$this->isScoped() )
      return $this->getScopedColumns();

    $prefix = $this->getTable() . '.';

    return array_map(function($c) use ($prefix) {
      return $prefix . $c; }, $this->getScopedColumns());
  }

  /**
   * Returns wether this particular node instance is scoped by certain fields
   * or not.
   *
   * @return boolean
   */
  public function isScoped() {
    return !!(count($this->getScopedColumns()) > 0);
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
    return $this->hasMany(get_class($this), $this->getParentColumnName())
                ->orderBy($this->getOrderColumnName());
  }

  /**
   * Get a new "scoped" query builder for the Node's model.
   *
   * @param  bool  $excludeDeleted
   * @return \Illuminate\Database\Eloquent\Builder|static
   */
  public function newNestedSetQuery($excludeDeleted = true) {
    $builder = $this->newQuery($excludeDeleted)->orderBy($this->getQualifiedOrderColumnName());

    if ( $this->isScoped() ) {
      foreach($this->scoped as $scopeFld)
        $builder->where($scopeFld, '=', $this->$scopeFld);
    }

    return $builder;
  }

  /**
   * Overload new Collection
   *
   * @param array $models
   * @return \Baum\Extensions\Eloquent\Collection
   */
  public function newCollection(array $models = array()) {
    return new Collection($models);
  }

  /**
   * Get all of the nodes from the database.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection|static[]
   */
  public static function all($columns = array('*')) {
    $instance = new static;

    return $instance->newQuery()
                    ->orderBy($instance->getQualifiedOrderColumnName())
                    ->get();
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
                    ->orderBy($instance->getQualifiedOrderColumnName());
  }

  /**
   * Static query scope. Returns a query scope with all nodes which are at
   * the end of a branch.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public static function allLeaves() {
    $instance = new static;

    $grammar = $instance->getConnection()->getQueryGrammar();

    $rgtCol = $grammar->wrap($instance->getQualifiedRightColumnName());
    $lftCol = $grammar->wrap($instance->getQualifiedLeftColumnName());

    return $instance->newQuery()
                    ->whereRaw($rgtCol . ' - ' . $lftCol . ' = 1')
                    ->orderBy($instance->getQualifiedOrderColumnName());
  }

  /**
   * Static query scope. Returns a query scope with all nodes which are at
   * the middle of a branch (not root and not leaves).
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public static function allTrunks() {
    $instance = new static;

    $grammar = $instance->getConnection()->getQueryGrammar();

    $rgtCol = $grammar->wrap($instance->getQualifiedRightColumnName());
    $lftCol = $grammar->wrap($instance->getQualifiedLeftColumnName());

    return $instance->newQuery()
                    ->whereNotNull($instance->getParentColumnName())
                    ->whereRaw($rgtCol . ' - ' . $lftCol . ' != 1')
                    ->orderBy($instance->getQualifiedOrderColumnName());
  }

  /**
   * Checks wether the underlying Nested Set structure is valid.
   *
   * @return boolean
   */
  public static function isValidNestedSet() {
    $validator = new SetValidator(new static);

    return $validator->passes();
  }

  /**
   * Rebuilds the structure of the current Nested Set.
   *
   * @param  bool $force
   * @return void
   */
  public static function rebuild($force = false) {
    $builder = new SetBuilder(new static);

    $builder->rebuild($force);
  }

  /**
   * Maps the provided tree structure into the database.
   *
   * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
   * @return  boolean
   */
  public static function buildTree($nodeList) {
    return with(new static)->makeTree($nodeList);
  }

  /**
   * Query scope which extracts a certain node object from the current query
   * expression.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function scopeWithoutNode($query, $node) {
    return $query->where($node->getKeyName(), '!=', $node->getKey());
  }

  /**
   * Extracts current node (self) from current query expression.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function scopeWithoutSelf($query) {
    return $this->scopeWithoutNode($query, $this);
  }

  /**
   * Extracts first root (from the current node p-o-v) from current query
   * expression.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function scopeWithoutRoot($query) {
    return $this->scopeWithoutNode($query, $this->getRoot());
  }

  /**
   * Provides a depth level limit for the query.
   *
   * @param   query   \Illuminate\Database\Query\Builder
   * @param   limit   integer
   * @return  \Illuminate\Database\Query\Builder
   */
  public function scopeLimitDepth($query, $limit) {
    $depth  = $this->exists ? $this->getDepth() : $this->getLevel();
    $max    = $depth + $limit;

    return $query->whereBetween($this->getDepthColumnName(), array($depth, $max));
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
   * Returns true if this is a trunk node (not root or leaf).
   *
   * @return boolean
   */
  public function isTrunk() {
    return !$this->isRoot() && !$this->isLeaf();
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
    return $this->newNestedSetQuery()
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
   * Get all the ancestor chain from the database including the current node
   * but without the root node.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getAncestorsAndSelfWithoutRoot($columns = array('*')) {
    return $this->ancestorsAndSelf()->withoutRoot()->get($columns);
  }

  /**
   * Instance scope which targets all the ancestor chain nodes excluding
   * the current one.
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function ancestors() {
    return $this->ancestorsAndSelf()->withoutSelf();
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
   * Get all the ancestor chain from the database excluding the current node
   * and the root node (from the current node's perspective).
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getAncestorsWithoutRoot($columns = array('*')) {
    return $this->ancestors()->withoutRoot()->get($columns);
  }

  /**
   * Instance scope which targets all children of the parent, including self.
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function siblingsAndSelf() {
    return $this->newNestedSetQuery()
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
    return $this->siblingsAndSelf()->withoutSelf();
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
    $grammar = $this->getConnection()->getQueryGrammar();

    $rgtCol = $grammar->wrap($this->getQualifiedRightColumnName());
    $lftCol = $grammar->wrap($this->getQualifiedLeftColumnName());

    return $this->descendants()
                ->whereRaw($rgtCol . ' - ' . $lftCol . ' = 1');
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
   * Instance scope targeting all of its nested children which are between the
   * root and the leaf nodes (middle branch).
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function trunks() {
    $grammar = $this->getConnection()->getQueryGrammar();

    $rgtCol = $grammar->wrap($this->getQualifiedRightColumnName());
    $lftCol = $grammar->wrap($this->getQualifiedLeftColumnName());

    return $this->descendants()
                ->whereNotNull($this->getQualifiedParentColumnName())
                ->whereRaw($rgtCol . ' - ' . $lftCol . ' != 1');
  }

  /**
   * Return all of its nested children which are trunks.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getTrunks($columns = array('*')) {
    return $this->trunks()->get($columns);
  }

  /**
   * Scope targeting itself and all of its nested children.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function descendantsAndSelf() {
    return $this->newNestedSetQuery()
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
    if ( is_array($columns) )
      return $this->descendantsAndSelf()->get($columns);

    $arguments = func_get_args();

    $limit    = intval(array_shift($arguments));
    $columns  = array_shift($arguments) ?: array('*');

    return $this->descendantsAndSelf()->limitDepth($limit)->get($columns);
  }

  /**
   * Set of all children & nested children.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function descendants() {
    return $this->descendantsAndSelf()->withoutSelf();
  }

  /**
   * Retrieve all of its children & nested children.
   *
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getDescendants($columns = array('*')) {
    if ( is_array($columns) )
      return $this->descendants()->get($columns);

    $arguments = func_get_args();

    $limit    = intval(array_shift($arguments));
    $columns  = array_shift($arguments) ?: array('*');

    return $this->descendants()->limitDepth($limit)->get($columns);
  }

  /**
   * Set of "immediate" descendants (aka children), alias for the children relation.
   *
   * @return \Illuminate\Database\Query\Builder
   */
  public function immediateDescendants() {
    return $this->children();
  }

  /**
   * Retrive all of its "immediate" descendants.
   *
   * @param array   $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getImmediateDescendants($columns = array('*')) {
    return $this->children()->get($columns);
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

    return $this->computeLevel();
  }

  /**
   * Returns true if node is a descendant.
   *
   * @param NestedSet
   * @return boolean
   */
  public function isDescendantOf($other) {
    return (
      $this->getLeft() > $other->getLeft()  &&
      $this->getLeft() < $other->getRight() &&
      $this->inSameScope($other)
    );
  }

  /**
   * Returns true if node is self or a descendant.
   *
   * @param NestedSet
   * @return boolean
   */
  public function isSelfOrDescendantOf($other) {
   return (
      $this->getLeft() >= $other->getLeft() &&
      $this->getLeft() < $other->getRight() &&
      $this->inSameScope($other)
    );
  }

  /**
   * Returns true if node is an ancestor.
   *
   * @param NestedSet
   * @return boolean
   */
  public function isAncestorOf($other) {
    return (
      $this->getLeft() < $other->getLeft()  &&
      $this->getRight() > $other->getLeft() &&
      $this->inSameScope($other)
    );
  }

  /**
   * Returns true if node is self or an ancestor.
   *
   * @param NestedSet
   * @return boolean
   */
  public function isSelfOrAncestorOf($other) {
   return (
      $this->getLeft() <= $other->getLeft() &&
      $this->getRight() > $other->getLeft() &&
      $this->inSameScope($other)
    );
  }

  /**
   * Returns the first sibling to the left.
   *
   * @return NestedSet
   */
  public function getLeftSibling() {
    return $this->siblings()
                ->where($this->getLeftColumnName(), '<', $this->getLeft())
                ->orderBy($this->getOrderColumnName(), 'desc')
                ->get()
                ->last();
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
   * Alias for moveToRightOf
   *
   * @return \Baum\Node
   */
  public function makeNextSiblingOf($node) {
    return $this->moveToRightOf($node);
  }

  /**
   * Alias for moveToRightOf
   *
   * @return \Baum\Node
   */
  public function makeSiblingOf($node) {
    return $this->moveToRightOf($node);
  }

  /**
   * Alias for moveToLeftOf
   *
   * @return \Baum\Node
   */
  public function makePreviousSiblingOf($node) {
    return $this->moveToLeftOf($node);
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
   * Make the node the first child of ...
   *
   * @return \Baum\Node
   */
  public function makeFirstChildOf($node) {
    if ( $node->children()->count() == 0 )
      return $this->makeChildOf($node);

    return $this->moveToLeftOf($node->children()->first());
  }

  /**
   * Make the node the last child of ...
   *
   * @return \Baum\Node
   */
  public function makeLastChildOf($node) {
    return $this->makeChildOf($node);
  }

  /**
   * Make current node a root node.
   *
   * @return \Baum\Node
   */
  public function makeRoot() {
    return $this->moveTo($this, 'root');
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
   * Checkes if the given node is in the same scope as the current one.
   *
   * @param \Baum\Node
   * @return boolean
   */
  public function inSameScope($other) {
    foreach($this->getScopedColumns() as $fld) {
      if ( $this->$fld != $other->$fld ) return false;
    }

    return true;
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
      $this->getLeft()  >= $node->getLeft()   &&
      $this->getLeft()  <= $node->getRight()  &&
      $this->getRight() >= $node->getLeft()   &&
      $this->getRight() <= $node->getRight()
    );
  }

  /**
   * Sets default values for left and right fields.
   *
   * @return void
   */
  public function setDefaultLeftAndRight() {
    $withHighestRight = $this->newNestedSetQuery()->reOrderBy($this->getRightColumnName(), 'desc')->take(1)->sharedLock()->first();

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
  public function storeNewParent() {
    if ( $this->isDirty($this->getParentColumnName()) && ($this->exists || !$this->isRoot()) )
      static::$moveToNewParentId = $this->getParentId();
    else
      static::$moveToNewParentId = FALSE;
  }

  /**
   * Move to the new parent if appropiate.
   *
   * @return void
   */
  public function moveToNewParent() {
    $pid = static::$moveToNewParentId;

    if ( is_null($pid) )
      $this->makeRoot();
    else if ( $pid !== FALSE )
      $this->makeChildOf($pid);
  }

  /**
   * Sets the depth attribute
   *
   * @return \Baum\Node
   */
  public function setDepth() {
    $self = $this;

    $this->getConnection()->transaction(function() use ($self) {
      $self->reload();

      $level = $self->getLevel();

      $self->newNestedSetQuery()->where($self->getKeyName(), '=', $self->getKey())->update(array($self->getDepthColumnName() => $level));
      $self->setAttribute($self->getDepthColumnName(), $level);
    });

    return $this;
  }

  /**
   * Sets the depth attribute for the current node and all of its descendants.
   *
   * @return \Baum\Node
   */
  public function setDepthWithSubtree() {
    $self = $this;

    $this->getConnection()->transaction(function() use ($self) {
      $self->reload();

      $self->descendantsAndSelf()->select($self->getKeyName())->lockForUpdate()->get();

      $oldDepth = !is_null($self->getDepth()) ? $self->getDepth() : 0;

      $newDepth = $self->getLevel();

      $self->newNestedSetQuery()->where($self->getKeyName(), '=', $self->getKey())->update(array($self->getDepthColumnName() => $newDepth));
      $self->setAttribute($self->getDepthColumnName(), $newDepth);

      $diff = $newDepth - $oldDepth;
      if ( !$self->isLeaf() && $diff != 0 )
        $self->descendants()->increment($self->getDepthColumnName(), $diff);
    });

    return $this;
  }

  /**
   * Prunes a branch off the tree, shifting all the elements on the right
   * back to the left so the counts work.
   *
   * @return void;
   */
  public function destroyDescendants() {
    if ( is_null($this->getRight()) || is_null($this->getLeft()) ) return;

    $self = $this;

    $this->getConnection()->transaction(function() use ($self) {
      $self->reload();

      $lftCol = $self->getLeftColumnName();
      $rgtCol = $self->getRightColumnName();
      $lft    = $self->getLeft();
      $rgt    = $self->getRight();

      // Apply a lock to the rows which fall past the deletion point
      $self->newNestedSetQuery()->where($lftCol, '>=', $lft)->select($self->getKeyName())->lockForUpdate()->get();

      // Prune children
      $self->newNestedSetQuery()->where($lftCol, '>', $lft)->where($rgtCol, '<', $rgt)->delete();

      // Update left and right indexes for the remaining nodes
      $diff = $rgt - $lft + 1;

      $self->newNestedSetQuery()->where($lftCol, '>', $rgt)->decrement($lftCol, $diff);
      $self->newNestedSetQuery()->where($rgtCol, '>', $rgt)->decrement($rgtCol, $diff);
    });
  }

  /**
   * "Makes room" for the the current node between its siblings.
   *
   * @return void
   */
  public function shiftSiblingsForRestore() {
    if ( is_null($this->getRight()) || is_null($this->getLeft()) ) return;

    $self = $this;

    $this->getConnection()->transaction(function() use ($self) {
      $lftCol = $self->getLeftColumnName();
      $rgtCol = $self->getRightColumnName();
      $lft    = $self->getLeft();
      $rgt    = $self->getRight();

      $diff = $rgt - $lft + 1;

      $self->newNestedSetQuery()->where($lftCol, '>=', $lft)->increment($lftCol, $diff);
      $self->newNestedSetQuery()->where($rgtCol, '>=', $lft)->increment($rgtCol, $diff);
    });
  }

  /**
   * Restores all of the current node's descendants.
   *
   * @return void
   */
  public function restoreDescendants() {
    if ( is_null($this->getRight()) || is_null($this->getLeft()) ) return;

    $self = $this;

    $this->getConnection()->transaction(function() use ($self) {
      $self->newNestedSetQuery()
        ->withTrashed()
        ->where($self->getLeftColumnName(), '>', $self->getLeft())
        ->where($self->getRightColumnName(), '<', $self->getRight())
        ->update(array(
          $self->getDeletedAtColumn() => null,
          $self->getUpdatedAtColumn() => $self->{$self->getUpdatedAtColumn()}
        ));
    });
  }

  /**
   * Return an key-value array indicating the node's depth with $seperator
   *
   * @return Array
   */
  public static function getNestedList($column, $key = null, $seperator = ' ') {
    $instance = new static;

    $key = $key ?: $instance->getKeyName();
    $depthColumn = $instance->getDepthColumnName();

    $nodes = $instance->newNestedSetQuery()->get()->toArray();

    return array_combine(array_map(function($node) use($key) {
      return $node[$key];
    }, $nodes), array_map(function($node) use($seperator, $depthColumn, $column) {
      return str_repeat($seperator, $node[$depthColumn]) . $node[$column];
    }, $nodes));
  }

  /**
   * Maps the provided tree structure into the database using the current node
   * as the parent. The provided tree structure will be inserted/updated as the
   * descendancy subtree of the current node instance.
   *
   * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
   * @return  boolean
   */
  public function makeTree($nodeList) {
    $mapper = new SetMapper($this);

    return $mapper->map($nodeList);
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

  /**
   * Compute current node level. If could not move past ourseleves return
   * our ancestor count, otherwhise get the first parent level + the computed
   * nesting.
   *
   * @return integer
   */
  protected function computeLevel() {
    list($node, $nesting) = $this->determineDepth($this);

    if ( $node->equals($this) )
      return $this->ancestors()->count();

    return $node->getLevel() + $nesting;
  }

  /**
   * Return an array with the last node we could reach and its nesting level
   *
   * @param   Baum\Node $node
   * @param   integer   $nesting
   * @return  array
   */
  protected function determineDepth($node, $nesting = 0) {
    // Traverse back up the ancestry chain and add to the nesting level count
    while( $parent = $node->parent()->first() ) {
      $nesting = $nesting + 1;

      $node = $parent;
    }

    return array($node, $nesting);
  }

}
