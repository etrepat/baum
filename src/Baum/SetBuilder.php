<?php
namespace Baum;

use Baum\Helpers\DatabaseHelper as DB;
use Baum\Node;

class SetBuilder {

  /**
  * Node instance for reference
  *
  * @var \Baum\Node
  */
  protected $node = NULL;

  /**
   * Array which will hold temporary lft, rgt index values for each scope.
   *
   * @var array
   */
  protected $bounds = array();

  /**
   * Create a new \Baum\SetBuilder class instance.
   *
   * @param   \Baum\Node      $node
   * @return  void
   */
  public function __construct($node) {
    $this->node = $node;
  }

  /**
   * Perform the re-calculation of the left and right indexes of the whole
   * nested set tree structure.
   *
   * @param  bool $force
   * @return void
   */
  public function rebuild($force = false) {
    $alreadyValid = forward_static_call(array(get_class($this->node), 'isValidNestedSet'));

    // Do not rebuild a valid Nested Set tree structure
    if ( !$force && $alreadyValid ) return true;

    // Rebuild lefts and rights for each root node and its children (recursively).
    // We go by setting left (and keep track of the current left bound), then
    // search for each children and recursively set the left index (while
    // incrementing that index). When going back up the recursive chain we start
    // setting the right indexes and saving the nodes...
    $self = $this;

    $this->node->getConnection()->transaction(function() use ($self) {
      foreach($self->roots() as $root)
        $self->rebuildBounds($root, 0);
    });
  }

  /**
   * Return all root nodes for the current database table appropiately sorted.
   *
   * @return Illuminate\Database\Eloquent\Collection
   */
  public function roots() {
    return $this->node->newQuery()
      ->whereNull($this->node->getQualifiedParentColumnName())
      ->orderBy($this->node->getQualifiedLeftColumnName())
      ->orderBy($this->node->getQualifiedRightColumnName())
      ->orderBy($this->node->getQualifiedKeyName())
      ->get();
  }

  /**
   * Recompute left and right index bounds for the specified node and its
   * children (recursive call). Fill the depth column too.
   */
  public function rebuildBounds($node, $depth = 0) {
    $k = $this->scopedKey($node);

    $node->setAttribute($node->getLeftColumnName(), $this->getNextBound($k));
    $node->setAttribute($node->getDepthColumnName(), $depth);

    foreach($this->children($node) as $child)
      $this->rebuildBounds($child, $depth + 1);

    $node->setAttribute($node->getRightColumnName(), $this->getNextBound($k));

    $node->save();
  }

  /**
   * Return all children for the specified node.
   *
   * @param   Baum\Node $node
   * @return  Illuminate\Database\Eloquent\Collection
   */
  public function children($node) {
    $query = $this->node->newQuery();

    $query->where($this->node->getQualifiedParentColumnName(), '=', $node->getKey());

    // We must also add the scoped column values to the query to compute valid
    // left and right indexes.
    foreach($this->scopedAttributes($node) as $fld => $value)
      $query->where($this->qualify($fld), '=', $value);

    $query->orderBy($this->node->getQualifiedLeftColumnName());
    $query->orderBy($this->node->getQualifiedRightColumnName());
    $query->orderBy($this->node->getQualifiedKeyName());

    return $query->get();
  }

  /**
   * Return an array of the scoped attributes of the supplied node.
   *
   * @param   Baum\Node $node
   * @return  array
   */
  protected function scopedAttributes($node) {
    $keys = $this->node->getScopedColumns();

    if ( count($keys) == 0 )
      return array();

    $values = array_map(function($column) use ($node) {
      return $node->getAttribute($column); }, $keys);

    return array_combine($keys, $values);
  }

  /**
   * Return a string-key for the current scoped attributes. Used for index
   * computing when a scope is defined (acsts as an scope identifier).
   *
   * @param   Baum\Node $node
   * @return  string
   */
  protected function scopedKey($node) {
    $attributes = $this->scopedAttributes($node);

    $output = array();

    foreach($attributes as $fld => $value)
      $output[] = $this->qualify($fld).'='.(is_null($value) ? 'NULL' : $value);

    // NOTE: Maybe an md5 or something would be better. Should be unique though.
    return implode(',', $output);
  }

  /**
   * Return next index bound value for the given key (current scope identifier)
   *
   * @param   string  $key
   * @return  integer
   */
  protected function getNextBound($key) {
    if ( false === array_key_exists($key, $this->bounds) )
      $this->bounds[$key] = 0;

    $this->bounds[$key] = $this->bounds[$key] + 1;

    return $this->bounds[$key];
  }

  /**
   * Get the fully qualified value for the specified column.
   *
   * @return string
   */
  protected function qualify($column) {
    return $this->node->getTable() . '.' . $column;
  }

}
