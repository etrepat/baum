<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

use Mockery as m;

class BaumTest extends PHPUnit_Framework_TestCase {

  public static function setUpBeforeClass() {
    Capsule::schema()->create('categories', function($t) {
      $t->increments('id');

      $t->integer('parent_id')->nullable();
      $t->integer('lft')->nullable();
      $t->integer('rgt')->nullable();
      $t->integer('depth')->nullable();

      $t->string('name', 255);

      $t->timestamps();
    });
  }

  public function setUp() {
    Model::unguard();

    Category::create(['id' => 1, 'name' => 'Root 1'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0]);
    Category::create(['id' => 2, 'name' => 'Child 1'  , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 1]);
    Category::create(['id' => 3, 'name' => 'Child 2'  , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 1]);
    Category::create(['id' => 4, 'name' => 'Child 2.1', 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 3]);
    Category::create(['id' => 5, 'name' => 'Child 3'  , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 1]);
    Category::create(['id' => 6, 'name' => 'Root 2'   , 'lft' => 11 , 'rgt' => 12 , 'depth' => 0]);

    Model::reguard();
  }

  public function tearDown() {
    Capsule::table('categories')->delete();

    m::close();
  }

  protected function categories($name) {
    return Category::where('name', '=', $name)->first();
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
    $parent = Category::first();

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

  public function testGetLevel() {
    $this->assertEquals(0, $this->categories('Root 1')->getLevel());
    $this->assertEquals(1, $this->categories('Child 1')->getLevel());
    $this->assertEquals(2, $this->categories('Child 2.1')->getLevel());
  }

  public function testDepthIsUpdatedWhenMadeChild() {
    $a = Category::create(['name' => 'A']);
    $b = Category::create(['name' => 'B']);
    $c = Category::create(['name' => 'C']);
    $d = Category::create(['name' => 'D']);

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
    $a = Category::create(['name' => 'A']);
    $b = Category::create(['name' => 'B']);
    $c = Category::create(['name' => 'C']);
    $d = Category::create(['name' => 'D']);

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
    $a = Category::create(['name' => 'A']);
    $b = Category::create(['name' => 'B']);
    $c = Category::create(['name' => 'C']);

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

  public function testMoveRight() {
    $this->categories('Child 2')->moveRight();

    $this->assertNull($this->categories('Child 2')->getRightSibling());

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 2')->getLeftSibling());
  }

  public function testMoveToLeftOf() {
    $this->categories('Child 3')->moveToLeftOf($this->categories('Child 1'));

    $this->assertNull($this->categories('Child 3')->getLeftSibling());

    $this->assertEquals($this->categories('Child 1'), $this->categories('Child 3')->getRightSibling());
  }

  public function testMoveToRightOf() {
    $this->categories('Child 1')->moveToRightOf($this->categories('Child 3'));

    $this->assertNull($this->categories('Child 1')->getRightSibling());

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->getLeftSibling());
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
    $newChild = Category::create(['name' => 'Child 4']);

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
    $newRoot = Category::create(['name' => 'Root 3']);

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
    $newRoot = Category::create(['name' => 'Root 3']);

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

    Model::setEventDispatcher($dispatcher);
  }

  public function testMovementHaltsWhenReturningFalseFromMoving() {
    Category::moving(function($node) { return false; });

    $dispatcher = Model::getEventDispatcher();
    Model::setEventDispatcher($events = m::mock('Illuminate\Events\Dispatcher'));

    $unchanged = $this->categories('Child 2');

    $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($unchanged), $unchanged)->andReturn(false);

    $unchanged->makeRoot();

    $unchanged->reload();

    $this->assertEquals(1, $unchanged->getParentId());
    $this->assertEquals(1, $unchanged->getLevel());
    $this->assertEquals(4, $unchanged->getLeft());
    $this->assertEquals(7, $unchanged->getRight());

    Model::setEventDispatcher($dispatcher);
  }

}
