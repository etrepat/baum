<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Mockery as m;

class BaumTest extends PHPUnit_Framework_TestCase {

  public static function setUpBeforeClass() {
    Capsule::schema()->dropIfExists('categories');

    Capsule::schema()->create('categories', function($t) {
      $t->increments('id');

      $t->integer('parent_id')->nullable();
      $t->integer('lft')->nullable();
      $t->integer('rgt')->nullable();
      $t->integer('depth')->nullable();

      $t->string('name', 255);

      $t->integer('company_id')->unsigned()->nullable();
    });

    Capsule::schema()->dropIfExists('menus');

    Capsule::schema()->create('menus', function($t) {
      $t->increments('id');

      $t->string('caption', 255);

      $t->integer('parent_id')->nullable();
      $t->integer('lft')->nullable();
      $t->integer('rgt')->nullable();
      $t->integer('depth')->nullable();

      $t->integer('site_id')->unsigned()->nullable();
      $t->string('language', 3)->nullable();
    });
  }

  public function setUp() {
    Model::unguard();

    Category::create(array('id' => 1, 'name' => 'Root 1'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    Category::create(array('id' => 2, 'name' => 'Child 1'  , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 1));
    Category::create(array('id' => 3, 'name' => 'Child 2'  , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 1));
    Category::create(array('id' => 4, 'name' => 'Child 2.1', 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 3));
    Category::create(array('id' => 5, 'name' => 'Child 3'  , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 1));
    Category::create(array('id' => 6, 'name' => 'Root 2'   , 'lft' => 11 , 'rgt' => 12 , 'depth' => 0));

    Model::reguard();

    if ( Capsule::connection()->getDriverName() === 'pgsql' ) {
      $tablePrefix = Capsule::connection()->getTablePrefix();

      $sequenceName = $tablePrefix . 'categories_id_seq';

      Capsule::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 7');
    }
  }

  public function tearDown() {
    Capsule::table('categories')->delete();
    Capsule::table('menus')->delete();

    m::close();
  }

  protected function categories($name) {
    return Category::where('name', '=', $name)->first();
  }

  protected function menus($caption) {
    return Menu::where('caption', '=', $caption)->first();
  }

  public function testGetParentColumnName() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getParentColumnName(), 'parent_id');
  }

  public function testGetQualifiedParentColumnName() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getQualifiedParentColumnName(), 'categories.parent_id');
  }

  public function testGetParentId() {
    $this->assertNull($this->categories('Root 1')->getParentId());
    $this->assertEquals($this->categories('Child 1')->getParentId(), 1);
  }

  public function testGetLeftColumnName() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getLeftColumnName(), 'lft');
  }

  public function testGetQualifiedLeftColumnName() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getQualifiedLeftColumnName(), 'categories.lft');
  }

  public function testGetLeft() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getLeft(), 1);
  }

  public function testGetRightColumnName() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getRightColumnName(), 'rgt');
  }

  public function testGetQualifiedRightColumnName() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getQualifiedRightColumnName(), 'categories.rgt');
  }

  public function testGetRight() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getRight(), 10);
  }

  public function testRootsStatic() {
    $query = Category::whereNull('parent_id')->get();
    $roots = Category::roots()->get();

    $this->assertEquals($query->count(), $roots->count());
    $this->assertCount(2, $roots);

    foreach ($query->lists('id') as $node)
      $this->assertContains($node, $roots->lists('id'));
  }

  public function testRootStatic() {
    $this->assertEquals(Category::root(), $this->categories('Root 1'));
  }

  public function testGetRootNonPersistedEqualsSelf() {
    $category = new Category;

    $this->assertEquals($category->getRoot(), $category);
  }

  public function testGetRootNonPersistedWhenSet() {
    $parent = Category::roots()->first();

    $child = new Category;
    $child->setAttribute($child->getParentColumnName(), $parent->getKey());

    $this->assertEquals($child->getRoot(), $parent);
  }

  public function testIsRoot() {
    $this->assertTrue($this->categories('Root 1')->isRoot());
    $this->assertTrue($this->categories('Root 2')->isRoot());

    $this->assertFalse($this->categories('Child 2')->isRoot());
  }

  public function testAllLeaves() {
    $allLeaves = Category::allLeaves()->get();

    $this->assertCount(4, $allLeaves);

    $leaves = $allLeaves->lists('name');

    $this->assertContains('Child 1', $leaves);
    $this->assertContains('Child 2.1', $leaves);
    $this->assertContains('Child 3', $leaves);
    $this->assertContains('Root 2', $leaves);
  }

  public function testGetNestedList() {
    $seperator = ' ';
    $nestedList = Category::getNestedList('name', 'id', $seperator);

    $expected = array(
      1 => str_repeat($seperator, 0). 'Root 1',
      2 => str_repeat($seperator, 1). 'Child 1',
      3 => str_repeat($seperator, 1). 'Child 2',
      4 => str_repeat($seperator, 2). 'Child 2.1',
      5 => str_repeat($seperator, 1). 'Child 3',
      6 => str_repeat($seperator, 0). 'Root 2',
    );

    $this->assertEquals($expected, $nestedList);
  }

  public function testIsLeaf() {
    $this->assertTrue($this->categories('Child 1')->isLeaf());
    $this->assertTrue($this->categories('Child 2.1')->isLeaf());
    $this->assertTrue($this->categories('Child 3')->isLeaf());
    $this->assertTrue($this->categories('Root 2')->isLeaf());

    $this->assertFalse($this->categories('Root 1')->isLeaf());

    $new = new Category;
    $this->assertFalse($new->isLeaf());
  }

  public function testParentRelation() {
    $parent = $this->categories('Child 2.1')->parent()->first();

    $this->assertEquals($this->categories('Child 2'), $parent);
  }

  public function testWithoutNodeScope() {
    $child = $this->categories('Child 2.1');

    $expected = array($this->categories('Root 1'), $child);

    $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutNode($this->categories('Child 2'))->get()->all());
  }

  public function testWithoutSelfScope() {
    $child = $this->categories('Child 2.1');

    $expected = array($this->categories('Root 1'), $this->categories('Child 2'));

    $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutSelf()->get()->all());
  }

  public function testWithoutRootScope() {
    $child = $this->categories('Child 2.1');

    $expected = array($this->categories('Child 2'), $child);

    $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutRoot()->get()->all());
  }

  public function testGetAncestorsAndSelf() {
    $child = $this->categories('Child 2.1');

    $expected = array($this->categories('Root 1'), $this->categories('Child 2'), $child);

    $this->assertEquals($expected, $child->getAncestorsAndSelf()->all());
  }

  public function testGetAncestors() {
    $child  = $this->categories('Child 2.1');

    $expected = array($this->categories('Root 1'), $this->categories('Child 2'));

    $this->assertEquals($expected, $child->getAncestors()->all());
  }

  public function testGetAncestorsAndSelfWithoutRoot() {
    $child = $this->categories('Child 2.1');

    $expected = array($this->categories('Child 2'), $child);

    $this->assertEquals($expected, $child->getAncestorsAndSelfWithoutRoot()->all());
  }

  public function testGetAncestorsWithoutRoot() {
    $child  = $this->categories('Child 2.1');

    $expected = array($this->categories('Child 2'));

    $this->assertEquals($expected, $child->getAncestorsWithoutRoot()->all());
  }

  public function testGetSiblingsAndSelf() {
    $child = $this->categories('Child 2');

    $expected = array($this->categories('Child 1'), $child, $this->categories('Child 3'));
    $this->assertEquals($expected, $child->getSiblingsAndSelf()->all());

    $expected = array($this->categories('Root 1'), $this->categories('Root 2'));
    $this->assertEquals($expected, $this->categories('Root 1')->getSiblingsAndSelf()->all());
  }

  public function testGetSiblings() {
    $child = $this->categories('Child 2');

    $expected = array($this->categories('Child 1'), $this->categories('Child 3'));

    $this->assertEquals($expected, $child->getSiblings()->all());
  }

  public function testGetLeaves() {
    $leaves = array($this->categories('Child 1'), $this->categories('Child 2.1'), $this->categories('Child 3'));

    $this->assertEquals($leaves, $this->categories('Root 1')->getLeaves()->all());
  }

  public function testGetLeavesIteration() {
    $node = $this->categories('Root 1');

    $expectedIds = array(2, 4, 5);

    foreach($node->getLeaves() as $i => $leaf)
      $this->assertEquals($expectedIds[$i], $leaf->id);
  }

  public function testGetLevel() {
    $this->assertEquals(0, $this->categories('Root 1')->getLevel());
    $this->assertEquals(1, $this->categories('Child 1')->getLevel());
    $this->assertEquals(2, $this->categories('Child 2.1')->getLevel());
  }

  public function testDepthIsUpdatedWhenMadeChild() {
    $a = Category::create(array('name' => 'A'));
    $b = Category::create(array('name' => 'B'));
    $c = Category::create(array('name' => 'C'));
    $d = Category::create(array('name' => 'D'));

    // a > b > c > d
    $b->makeChildOf($a);
    $c->makeChildOf($b);
    $d->makeChildOf($c);

    $a->reload();
    $b->reload();
    $c->reload();
    $d->reload();

    $this->assertEquals(0, $a->getDepth());
    $this->assertEquals(1, $b->getDepth());
    $this->assertEquals(2, $c->getDepth());
    $this->assertEquals(3, $d->getDepth());
  }

  public function testDepthIsUpdatedOnDescendantsWhenParentMoves() {
    $a = Category::create(array('name' => 'A'));
    $b = Category::create(array('name' => 'B'));
    $c = Category::create(array('name' => 'C'));
    $d = Category::create(array('name' => 'D'));

    // a > b > c > d
    $b->makeChildOf($a);
    $c->makeChildOf($b);
    $d->makeChildOf($c);

    $a->reload(); $b->reload(); $c->reload(); $d->reload();

    $b->moveToRightOf($a);

    $a->reload(); $b->reload(); $c->reload(); $d->reload();

    $this->assertEquals(0, $b->getDepth());
    $this->assertEquals(1, $c->getDepth());
    $this->assertEquals(2, $d->getDepth());
  }

  public function testGetDescendantsAndSelf() {
    $parent = $this->categories('Root 1');

    $expected = array(
      $parent,
      $this->categories('Child 1'),
      $this->categories('Child 2'),
      $this->categories('Child 2.1'),
      $this->categories('Child 3')
    );

    $this->assertCount(count($expected), $parent->getDescendantsAndSelf());

    $this->assertEquals($expected, $parent->getDescendantsAndSelf()->all());
  }

  public function testGetDescendants() {
    $parent = $this->categories('Root 1');

    $expected = array(
      $this->categories('Child 1'),
      $this->categories('Child 2'),
      $this->categories('Child 2.1'),
      $this->categories('Child 3')
    );

    $this->assertCount(count($expected), $parent->getDescendants());

    $this->assertEquals($expected, $parent->getDescendants()->all());
  }

  public function testDescendantsRecursesChildren() {
    $a = Category::create(array('name' => 'A'));
    $b = Category::create(array('name' => 'B'));
    $c = Category::create(array('name' => 'C'));

    // a > b > c
    $b->makeChildOf($a);
    $c->makeChildOf($b);

    $a->reload(); $b->reload(); $c->reload();

    $this->assertEquals(1, $a->children()->count());
    $this->assertEquals(1, $b->children()->count());
    $this->assertEquals(2, $a->descendants()->count());
  }

  public function testChildrenRelation() {
    $root = $this->categories('Root 1');

    foreach($root->children() as $child)
      $this->assertEquals($root->getKey(), $child->getParentId());

    $expected = array($this->categories('Child 1'), $this->categories('Child 2'), $this->categories('Child 3'));

    $this->assertEquals($expected, $root->children()->get()->all());
  }

  public function testChildrenRespectDefaultOrdering() {
    $this->categories('Child 2')->moveLeft();

    $children = $this->categories('Root 1')->children()->get()->all();

    $this->assertEquals($children[0], $this->categories('Child 2'));
    $this->assertEquals($children[1], $this->categories('Child 1'));
    $this->assertEquals($children[2], $this->categories('Child 3'));
  }

  public function testCreateChildrenWithRelation() {
    $child = new Category(array('name' => 'Child 3.1'));

    $this->categories('Child 3')->children()->save($child);

    $this->assertTrue($child->exists);
    $this->assertEquals($this->categories('Child 3')->getKey(), $child->getParentId());
  }

  public function testGetImmediateDescendants() {
    $expected = array($this->categories('Child 1'), $this->categories('Child 2'), $this->categories('Child 3'));

    $this->assertEquals($expected, $this->categories('Root 1')->getImmediateDescendants()->all());

    $this->assertEquals(array($this->categories('Child 2.1')), $this->categories('Child 2')->getImmediateDescendants()->all());

    $this->assertEmpty($this->categories('Root 2')->getImmediateDescendants()->all());
  }

  public function testIsSelfOrAncestorOf() {
    $this->assertTrue($this->categories('Root 1')->isSelfOrAncestorOf($this->categories('Child 1')));
    $this->assertTrue($this->categories('Root 1')->isSelfOrAncestorOf($this->categories('Child 2.1')));
    $this->assertTrue($this->categories('Child 2')->isSelfOrAncestorOf($this->categories('Child 2.1')));
    $this->assertFalse($this->categories('Child 2.1')->isSelfOrAncestorOf($this->categories('Child 2')));
    $this->assertFalse($this->categories('Child 1')->isSelfOrAncestorOf($this->categories('Child 2')));
    $this->assertTrue($this->categories('Child 1')->isSelfOrAncestorOf($this->categories('Child 1')));
  }

  public function testIsAncestorOf() {
    $this->assertTrue($this->categories('Root 1')->isAncestorOf($this->categories('Child 1')));
    $this->assertTrue($this->categories('Root 1')->isAncestorOf($this->categories('Child 2.1')));
    $this->assertTrue($this->categories('Child 2')->isAncestorOf($this->categories('Child 2.1')));
    $this->assertFalse($this->categories('Child 2.1')->isAncestorOf($this->categories('Child 2')));
    $this->assertFalse($this->categories('Child 1')->isAncestorOf($this->categories('Child 2')));
    $this->assertFalse($this->categories('Child 1')->isAncestorOf($this->categories('Child 1')));
  }

  public function testIsSelfOrDescendantOf() {
    $this->assertTrue($this->categories('Child 1')->isSelfOrDescendantOf($this->categories('Root 1')));
    $this->assertTrue($this->categories('Child 2.1')->isSelfOrDescendantOf($this->categories('Root 1')));
    $this->assertTrue($this->categories('Child 2.1')->isSelfOrDescendantOf($this->categories('Child 2')));
    $this->assertFalse($this->categories('Child 2')->isSelfOrDescendantOf($this->categories('Child 2.1')));
    $this->assertFalse($this->categories('Child 2')->isSelfOrDescendantOf($this->categories('Child 1')));
    $this->assertTrue($this->categories('Child 1')->isSelfOrDescendantOf($this->categories('Child 1')));
  }

  public function testIsDescendantOf() {
    $this->assertTrue($this->categories('Child 1')->isDescendantOf($this->categories('Root 1')));
    $this->assertTrue($this->categories('Child 2.1')->isDescendantOf($this->categories('Root 1')));
    $this->assertTrue($this->categories('Child 2.1')->isDescendantOf($this->categories('Child 2')));
    $this->assertFalse($this->categories('Child 2')->isDescendantOf($this->categories('Child 2.1')));
    $this->assertFalse($this->categories('Child 2')->isDescendantOf($this->categories('Child 1')));
    $this->assertFalse($this->categories('Child 1')->isDescendantOf($this->categories('Child 1')));
  }

  public function testGetLeftSibling() {
    $this->assertEquals($this->categories('Child 1'), $this->categories('Child 2')->getLeftSibling());
    $this->assertEquals($this->categories('Child 2'), $this->categories('Child 3')->getLeftSibling());
  }

  public function testGetLeftSiblingOfFirstRootIsNull() {
    $this->assertNull($this->categories('Root 1')->getLeftSibling());
  }

  public function testGetLeftSiblingWithNoneIsNull() {
    $this->assertNull($this->categories('Child 2.1')->getLeftSibling());
  }

  public function testGetLeftSiblingOfLeftmostNodeIsNull() {
    $this->assertNull($this->categories('Child 1')->getLeftSibling());
  }

  public function testGetRightSibling() {
    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 2')->getRightSibling());
    $this->assertEquals($this->categories('Child 2'), $this->categories('Child 1')->getRightSibling());
  }

  public function testGetRightSiblingOfRoots() {
    $this->assertEquals($this->categories('Root 2'), $this->categories('Root 1')->getRightSibling());
    $this->assertNull($this->categories('Root 2')->getRightSibling());
  }

  public function testGetRightSiblingWithNoneIsNull() {
    $this->assertNull($this->categories('Child 2.1')->getRightSibling());
  }

  public function testGetRightSiblingOfRightmostNodeIsNull() {
    $this->assertNull($this->categories('Child 3')->getRightSibling());
  }

  public function testMoveLeft() {
    $this->categories('Child 2')->moveLeft();

    $this->assertNull($this->categories('Child 2')->getLeftSibling());

    $this->assertEquals($this->categories('Child 1'), $this->categories('Child 2')->getRightSibling());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveLeftRaisesAnExceptionWhenNotPossible() {
    $node = $this->categories('Child 2');

    $node->moveLeft();
    $node->moveLeft();
  }

  public function testMoveRight() {
    $this->categories('Child 2')->moveRight();

    $this->assertNull($this->categories('Child 2')->getRightSibling());

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 2')->getLeftSibling());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveRightRaisesAnExceptionWhenNotPossible() {
    $node = $this->categories('Child 2');

    $node->moveRight();
    $node->moveRight();
  }

  public function testMoveToLeftOf() {
    $this->categories('Child 3')->moveToLeftOf($this->categories('Child 1'));

    $this->assertNull($this->categories('Child 3')->getLeftSibling());

    $this->assertEquals($this->categories('Child 1'), $this->categories('Child 3')->getRightSibling());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveToLeftOfRaisesAnExceptionWhenNotPossible() {
    $this->categories('Child 1')->moveToLeftOf($this->categories('Child 1')->getLeftSibling());
  }

  public function testMoveToRightOf() {
    $this->categories('Child 1')->moveToRightOf($this->categories('Child 3'));

    $this->assertNull($this->categories('Child 1')->getRightSibling());

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->getLeftSibling());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveToRightOfRaisesAnExceptionWhenNotPossible() {
    $this->categories('Child 3')->moveToRightOf($this->categories('Child 3')->getRightSibling());
  }

  public function testMakeRoot() {
    $this->categories('Child 2')->makeRoot();

    $newRoot = $this->categories('Child 2');

    $this->assertNull($newRoot->parent()->first());
    $this->assertEquals(0, $newRoot->getLevel());
    $this->assertEquals(7, $newRoot->getLeft());
    $this->assertEquals(10, $newRoot->getRight());

    $this->assertEquals(1, $this->categories('Child 2.1')->getLevel());
  }

  public function testMakeChildOf() {
    $this->categories('Child 1')->makeChildOf($this->categories('Child 3'));

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());
  }

  public function testMakeChildOfAppendsAtTheEnd() {
    $newChild = Category::create(array('name' => 'Child 4'));

    $newChild->makeChildOf($this->categories('Root 1'));

    $lastChild = $this->categories('Root 1')->children()->get()->last();
    $this->assertEquals($newChild, $lastChild);
  }

  public function testMakeChildOfMovesWithSubtree() {
    $this->categories('Child 2')->makeChildOf($this->categories('Child 1'));

    $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentId());

    $this->assertEquals(3, $this->categories('Child 2')->getLeft());
    $this->assertEquals(6, $this->categories('Child 2')->getRight());

    $this->assertEquals(2, $this->categories('Child 1')->getLeft());
    $this->assertEquals(7, $this->categories('Child 1')->getRight());
  }

  public function testMakeChildOfSwappingRoots() {
    $newRoot = Category::create(array('name' => 'Root 3'));

    $this->assertEquals(13, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());

    $this->categories('Root 2')->makeChildOf($newRoot);

    $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentId());

    $this->assertEquals(12, $this->categories('Root 2')->getLeft());
    $this->assertEquals(13, $this->categories('Root 2')->getRight());

    $this->assertEquals(11, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());
  }

  public function testMakeChildOfSwappingRootsWithSubtrees() {
    $newRoot = Category::create(array('name' => 'Root 3'));

    $this->categories('Root 1')->makeChildOf($newRoot);

    $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentId());

    $this->assertEquals(4, $this->categories('Root 1')->getLeft());
    $this->assertEquals(13, $this->categories('Root 1')->getRight());

    $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
    $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
  }

  public function testMovementEventsFire() {
    $dispatcher = Model::getEventDispatcher();
    Model::setEventDispatcher($events = m::mock('Illuminate\Events\Dispatcher'));

    $child = $this->categories('Child 1');

    $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($child), $child)->andReturn(true);
    $events->shouldReceive('fire')->once()->with('eloquent.moved: '.get_class($child), $child)->andReturn(true);

    $child->moveToRightOf($this->categories('Child 3'));

    Model::unsetEventDispatcher();
    Model::setEventDispatcher($dispatcher);
  }

  public function testMovementHaltsWhenReturningFalseFromMoving() {
    $unchanged = $this->categories('Child 2');

    $dispatcher = Model::getEventDispatcher();

    Model::setEventDispatcher($events = m::mock('Illuminate\Events\Dispatcher[until]'));
    $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($unchanged), $unchanged)->andReturn(false);

    // Force "moving" to return false
    Category::moving(function($node) { return false; });

    $unchanged->makeRoot();

    $unchanged->reload();

    $this->assertEquals(1, $unchanged->getParentId());
    $this->assertEquals(1, $unchanged->getLevel());
    $this->assertEquals(4, $unchanged->getLeft());
    $this->assertEquals(7, $unchanged->getRight());

    // Restore
    Model::getEventDispatcher()->forget('eloquent.moving: '.get_class($unchanged));

    Model::unsetEventDispatcher();
    Model::setEventDispatcher($dispatcher);
  }

  public function testInsideSubtree() {
    $this->assertFalse($this->categories('Child 1')->insideSubtree($this->categories('Root 2')));
    $this->assertFalse($this->categories('Child 2')->insideSubtree($this->categories('Root 2')));
    $this->assertFalse($this->categories('Child 3')->insideSubtree($this->categories('Root 2')));

    $this->assertTrue($this->categories('Child 1')->insideSubtree($this->categories('Root 1')));
    $this->assertTrue($this->categories('Child 2')->insideSubtree($this->categories('Root 1')));
    $this->assertTrue($this->categories('Child 2.1')->insideSubtree($this->categories('Root 1')));
    $this->assertTrue($this->categories('Child 3')->insideSubtree($this->categories('Root 1')));

    $this->assertTrue($this->categories('Child 2.1')->insideSubtree($this->categories('Child 2')));
    $this->assertFalse($this->categories('Child 2.1')->insideSubtree($this->categories('Root 2')));
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testUnpersistedNodeCannotBeMoved() {
    $unpersisted = new Category(array('name' => 'Unpersisted'));

    $unpersisted->moveToRightOf($this->categories('Root 1'));
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testUnpersistedNodeCannotBeMadeChild() {
    $unpersisted = new Category(array('name' => 'Unpersisted'));

    $unpersisted->makeChildOf($this->categories('Root 1'));
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotBeMovedToItself() {
    $node = $this->categories('Child 1');

    $node->moveToRightOf($node);
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotBeMadeChildOfThemselves() {
    $node = $this->categories('Child 1');

    $node->makeChildOf($node);
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotBeMovedToDescendantsOfThemselves() {
    $node = $this->categories('Root 1');

    $node->makeChildOf($this->categories('Child 2.1'));
  }

  public function testInSameScope() {
    $root   = ScopedCategory::root();
    $child  = $root->children()->first();

    $this->assertTrue($child->inSameScope($root));

    $child->update(array('company_id' => 75));

    $this->assertFalse($child->inSameScope($root));
  }

  public function testIsSelfOrAncestorOfScoped() {
    $root   = ScopedCategory::root();
    $child  = $root->children()->first();

    $this->assertTrue($root->isSelfOrAncestorOf($child));

    $child->update(array('company_id' => 75));
    $this->assertFalse($root->isSelfOrAncestorOf($child));
  }

  public function testIsSelfOrDescendantOfScoped() {
    $root   = ScopedCategory::root();
    $child  = $root->children()->first();

    $this->assertTrue($child->isSelfOrDescendantOf($root));

    $child->update(array('company_id' => 75));
    $this->assertFalse($child->isSelfOrDescendantOf($root));
  }

  public function testGetSiblingsAndSelfWithScope() {
    $menu1 = Menu::create(array('caption' => 'A', 'site_id' => 1, 'language' => 'en'));
    $menu2 = Menu::create(array('caption' => 'B', 'site_id' => 1, 'language' => 'en'));
    $menu3 = Menu::create(array('caption' => 'C', 'site_id' => 1, 'language' => 'es'));

    $menu1->reload();
    $menu2->reload();
    $menu3->reload();

    $expected = array($menu1, $menu2);
    $this->assertEquals($expected, $menu1->getSiblingsAndSelf()->all());

    $expected = array($menu3);
    $this->assertEquals($expected, $menu3->getSiblingsAndSelf()->all());
  }

  public function testSimpleMovementsWithScope() {
    $root   = Menu::create(array('caption' => 'R' , 'site_id' => 1, 'language' => 'en'));
    $child1 = Menu::create(array('caption' => 'C1', 'site_id' => 1, 'language' => 'en'));
    $child2 = Menu::create(array('caption' => 'C2', 'site_id' => 1, 'language' => 'en'));

    $child1->makeChildOf($root);
    $child2->makeChildOf($root);

    $this->assertEquals($root, $this->menus('R'));

    $expected = array($child1, $child2);
    $this->assertEquals($expected, $this->menus('R')->children()->get()->all());
  }

  public function testInSameScopeWithMultipleScopes() {
    $root1  = Menu::create(array('caption' => 'Root 1'  , 'site_id' => 1, 'language' => 'en'));
    $child1 = Menu::create(array('caption' => 'Child 1' , 'site_id' => 1, 'language' => 'en'));
    $root2  = Menu::create(array('caption' => 'Raíz 1'  , 'site_id' => 1, 'language' => 'es'));

    $child1->makeChildOf($root1);

    $this->assertTrue($this->menus('Root 1')->inSameScope($this->menus('Child 1')));
    $this->assertTrue($this->menus('Child 1')->inSameScope($this->menus('Root 1')));
    $this->assertFalse($this->menus('Root 1')->inSameScope($this->menus('Raíz 1')));
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotBeMovedBetweenScopes() {
    $root1  = Menu::create(array('caption' => 'Root 1'  , 'site_id' => 1, 'language' => 'en'));
    $child1 = Menu::create(array('caption' => 'Child 1' , 'site_id' => 1, 'language' => 'en'));
    $root2  = Menu::create(array('caption' => 'Raíz 1'  , 'site_id' => 1, 'language' => 'es'));
    $child2 = Menu::create(array('caption' => 'Hijo 1'  , 'site_id' => 1, 'language' => 'es'));

    $child1->makeChildOf($root1);
    $child2->makeChildOf($root2);

    $child2->makeChildOf($root1);
  }

  public function testMoveNodeBetweenScopes() {
    $root1    = Menu::create(array('caption' => 'TL1', 'site_id' => 1, 'language' => 'en'));
    $child11  = Menu::create(array('caption' => 'C11', 'site_id' => 1, 'language' => 'en'));
    $child12  = Menu::create(array('caption' => 'C12', 'site_id' => 1, 'language' => 'en'));
    $child11->makeChildOf($root1);
    $child12->makeChildOf($root1);

    $root2    = Menu::create(array('caption' => 'TL2', 'site_id' => 2, 'language' => 'en'));
    $child21  = Menu::create(array('caption' => 'C21', 'site_id' => 2, 'language' => 'en'));
    $child22  = Menu::create(array('caption' => 'C22', 'site_id' => 2, 'language' => 'en'));
    $child21->makeChildOf($root2);
    $child22->makeChildOf($root2);

    $child11->update(array('site_id' => 2));
    $child11->makeChildOf($root2);

    $expected = array($this->menus('C12'));
    $this->assertEquals($expected, $root1->children()->get()->all());

    $expected = array($this->menus('C21'), $this->menus('C22'), $this->menus('C11'));
    $this->assertEquals($expected, $root2->children()->get()->all());
  }

  public function testToHierarchyReturnsAnEloquentCollection() {
    $categories = Category::all()->toHierarchy();

    $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $categories);
  }

  public function testToHierarchyReturnsHierarchicalData() {
    $categories = Category::all()->toHierarchy();

    $this->assertEquals(2, $categories->count());

    $first = $categories->first();
    $this->assertEquals('Root 1', $first->name);
    $this->assertEquals(3, $first->children->count());

    $first_lvl2 = $first->children->first();
    $this->assertEquals('Child 1', $first_lvl2->name);
    $this->assertEquals(0, $first_lvl2->children->count());
  }

}
