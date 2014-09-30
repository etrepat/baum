<?php

class CategoryMovementTest extends CategoryTestCase {

  public function testMoveLeft() {
    $this->categories('Child 2')->moveLeft();

    $this->assertNull($this->categories('Child 2')->getLeftSibling());

    $this->assertEquals($this->categories('Child 1'), $this->categories('Child 2')->getRightSibling());

    $this->assertTrue(Category::isValidNestedSet());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveLeftRaisesAnExceptionWhenNotPossible() {
    $node = $this->categories('Child 2');

    $node->moveLeft();
    $node->moveLeft();
  }

  public function testMoveLeftDoesNotChangeDepth() {
    $this->categories('Child 2')->moveLeft();

    $this->assertEquals(1, $this->categories('Child 2')->getDepth());
    $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
  }

  public function testMoveLeftWithSubtree() {
    $this->categories('Root 2')->moveLeft();

    $this->assertNull($this->categories('Root 2')->getLeftSibling());
    $this->assertEquals($this->categories('Root 1'), $this->categories('Root 2')->getRightSibling());
    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals(0, $this->categories('Root 1')->getDepth());
    $this->assertEquals(0, $this->categories('Root 2')->getDepth());

    $this->assertEquals(1, $this->categories('Child 1')->getDepth());
    $this->assertEquals(1, $this->categories('Child 2')->getDepth());
    $this->assertEquals(1, $this->categories('Child 3')->getDepth());

    $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
  }

  public function testMoveToLeftOf() {
    $this->categories('Child 3')->moveToLeftOf($this->categories('Child 1'));

    $this->assertNull($this->categories('Child 3')->getLeftSibling());

    $this->assertEquals($this->categories('Child 1'), $this->categories('Child 3')->getRightSibling());

    $this->assertTrue(Category::isValidNestedSet());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveToLeftOfRaisesAnExceptionWhenNotPossible() {
    $this->categories('Child 1')->moveToLeftOf($this->categories('Child 1')->getLeftSibling());
  }

  public function testMoveToLeftOfDoesNotChangeDepth() {
    $this->categories('Child 2')->moveToLeftOf($this->categories('Child 1'));

    $this->assertEquals(1, $this->categories('Child 2')->getDepth());
    $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
  }

  public function testMoveToLeftOfWithSubtree() {
    $this->categories('Root 2')->moveToLeftOf($this->categories('Root 1'));

    $this->assertNull($this->categories('Root 2')->getLeftSibling());
    $this->assertEquals($this->categories('Root 1'), $this->categories('Root 2')->getRightSibling());
    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals(0, $this->categories('Root 1')->getDepth());
    $this->assertEquals(0, $this->categories('Root 2')->getDepth());

    $this->assertEquals(1, $this->categories('Child 1')->getDepth());
    $this->assertEquals(1, $this->categories('Child 2')->getDepth());
    $this->assertEquals(1, $this->categories('Child 3')->getDepth());

    $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
  }

  public function testMoveRight() {
    $this->categories('Child 2')->moveRight();

    $this->assertNull($this->categories('Child 2')->getRightSibling());

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 2')->getLeftSibling());

    $this->assertTrue(Category::isValidNestedSet());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveRightRaisesAnExceptionWhenNotPossible() {
    $node = $this->categories('Child 2');

    $node->moveRight();
    $node->moveRight();
  }

  public function testMoveRightDoesNotChangeDepth() {
    $this->categories('Child 2')->moveRight();

    $this->assertEquals(1, $this->categories('Child 2')->getDepth());
    $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
  }

  public function testMoveRightWithSubtree() {
    $this->categories('Root 1')->moveRight();

    $this->assertNull($this->categories('Root 1')->getRightSibling());
    $this->assertEquals($this->categories('Root 2'), $this->categories('Root 1')->getLeftSibling());
    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals(0, $this->categories('Root 1')->getDepth());
    $this->assertEquals(0, $this->categories('Root 2')->getDepth());

    $this->assertEquals(1, $this->categories('Child 1')->getDepth());
    $this->assertEquals(1, $this->categories('Child 2')->getDepth());
    $this->assertEquals(1, $this->categories('Child 3')->getDepth());

    $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
  }

  public function testMoveToRightOf() {
    $this->categories('Child 1')->moveToRightOf($this->categories('Child 3'));

    $this->assertNull($this->categories('Child 1')->getRightSibling());

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->getLeftSibling());

    $this->assertTrue(Category::isValidNestedSet());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveToRightOfRaisesAnExceptionWhenNotPossible() {
    $this->categories('Child 3')->moveToRightOf($this->categories('Child 3')->getRightSibling());
  }

  public function testMoveToRightOfDoesNotChangeDepth() {
    $this->categories('Child 2')->moveToRightOf($this->categories('Child 3'));

    $this->assertEquals(1, $this->categories('Child 2')->getDepth());
    $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
  }

  public function testMoveToRightOfWithSubtree() {
    $this->categories('Root 1')->moveToRightOf($this->categories('Root 2'));

    $this->assertNull($this->categories('Root 1')->getRightSibling());
    $this->assertEquals($this->categories('Root 2'), $this->categories('Root 1')->getLeftSibling());
    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals(0, $this->categories('Root 1')->getDepth());
    $this->assertEquals(0, $this->categories('Root 2')->getDepth());

    $this->assertEquals(1, $this->categories('Child 1')->getDepth());
    $this->assertEquals(1, $this->categories('Child 2')->getDepth());
    $this->assertEquals(1, $this->categories('Child 3')->getDepth());

    $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
  }

  public function testMakeRoot() {
    $this->categories('Child 2')->makeRoot();

    $newRoot = $this->categories('Child 2');

    $this->assertNull($newRoot->parent()->first());
    $this->assertEquals(0, $newRoot->getLevel());
    $this->assertEquals(9, $newRoot->getLeft());
    $this->assertEquals(12, $newRoot->getRight());

    $this->assertEquals(1, $this->categories('Child 2.1')->getLevel());

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testNullifyParentColumnMakesItRoot() {
    $node = $this->categories('Child 2');

    $node->parent_id = null;

    $node->save();

    $this->assertNull($node->parent()->first());
    $this->assertEquals(0, $node->getLevel());
    $this->assertEquals(9, $node->getLeft());
    $this->assertEquals(12, $node->getRight());

    $this->assertEquals(1, $this->categories('Child 2.1')->getLevel());

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testNullifyParentColumnOnNewNodes() {
    $node = new Category(['name' => 'Root 3']);

    $node->parent_id = null;

    $node->save();

    $node->reload();

    $this->assertNull($node->parent()->first());
    $this->assertEquals(0, $node->getLevel());
    $this->assertEquals(13, $node->getLeft());
    $this->assertEquals(14, $node->getRight());

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testNewCategoryWithNullParent() {
    $node = new Category(['name' => 'Root 3']);
    $this->assertTrue($node->isRoot());

    $node->save();
    $this->assertTrue($node->isRoot());

    $node->makeRoot();
    $this->assertTrue($node->isRoot());
  }

  public function testMakeChildOf() {
    $this->categories('Child 1')->makeChildOf($this->categories('Child 3'));

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testMakeChildOfAppendsAtTheEnd() {
    $newChild = Category::create(array('name' => 'Child 4'));

    $newChild->makeChildOf($this->categories('Root 1'));

    $lastChild = $this->categories('Root 1')->children()->get()->last();
    $this->assertEquals($newChild, $lastChild);

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testMakeChildOfMovesWithSubtree() {
    $this->categories('Child 2')->makeChildOf($this->categories('Child 1'));

    $this->assertTrue(Category::isValidNestedSet());

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

    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentId());

    $this->assertEquals(12, $this->categories('Root 2')->getLeft());
    $this->assertEquals(13, $this->categories('Root 2')->getRight());

    $this->assertEquals(11, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());
  }

  public function testMakeChildOfSwappingRootsWithSubtrees() {
    $newRoot = Category::create(array('name' => 'Root 3'));

    $this->categories('Root 1')->makeChildOf($newRoot);

    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentId());

    $this->assertEquals(4, $this->categories('Root 1')->getLeft());
    $this->assertEquals(13, $this->categories('Root 1')->getRight());

    $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
    $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
  }

  public function testMakeFirstChildOf() {
    $this->categories('Child 1')->makeFirstChildOf($this->categories('Child 3'));

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testMakeFirstChildOfAppendsAtTheBeginning() {
    $newChild = Category::create(array('name' => 'Child 4'));

    $newChild->makeFirstChildOf($this->categories('Root 1'));

    $lastChild = $this->categories('Root 1')->children()->get()->first();
    $this->assertEquals($newChild, $lastChild);

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testMakeFirstChildOfMovesWithSubtree() {
    $this->categories('Child 2')->makeFirstChildOf($this->categories('Child 1'));

    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentId());

    $this->assertEquals(3, $this->categories('Child 2')->getLeft());
    $this->assertEquals(6, $this->categories('Child 2')->getRight());

    $this->assertEquals(2, $this->categories('Child 1')->getLeft());
    $this->assertEquals(7, $this->categories('Child 1')->getRight());
  }

  public function testMakeFirstChildOfSwappingRoots() {
    $newRoot = Category::create(array('name' => 'Root 3'));

    $this->assertEquals(13, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());

    $this->categories('Root 2')->makeFirstChildOf($newRoot);

    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentId());

    $this->assertEquals(12, $this->categories('Root 2')->getLeft());
    $this->assertEquals(13, $this->categories('Root 2')->getRight());

    $this->assertEquals(11, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());
  }

  public function testMakeFirstChildOfSwappingRootsWithSubtrees() {
    $newRoot = Category::create(array('name' => 'Root 3'));

    $this->categories('Root 1')->makeFirstChildOf($newRoot);

    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentId());

    $this->assertEquals(4, $this->categories('Root 1')->getLeft());
    $this->assertEquals(13, $this->categories('Root 1')->getRight());

    $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
    $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
  }

  public function testMakeLastChildOf() {
    $this->categories('Child 1')->makeLastChildOf($this->categories('Child 3'));

    $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testMakeLastChildOfAppendsAtTheEnd() {
    $newChild = Category::create(array('name' => 'Child 4'));

    $newChild->makeLastChildOf($this->categories('Root 1'));

    $lastChild = $this->categories('Root 1')->children()->get()->last();
    $this->assertEquals($newChild, $lastChild);

    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testMakeLastChildOfMovesWithSubtree() {
    $this->categories('Child 2')->makeLastChildOf($this->categories('Child 1'));

    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentId());

    $this->assertEquals(3, $this->categories('Child 2')->getLeft());
    $this->assertEquals(6, $this->categories('Child 2')->getRight());

    $this->assertEquals(2, $this->categories('Child 1')->getLeft());
    $this->assertEquals(7, $this->categories('Child 1')->getRight());
  }

  public function testMakeLastChildOfSwappingRoots() {
    $newRoot = Category::create(array('name' => 'Root 3'));

    $this->assertEquals(13, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());

    $this->categories('Root 2')->makeLastChildOf($newRoot);

    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentId());

    $this->assertEquals(12, $this->categories('Root 2')->getLeft());
    $this->assertEquals(13, $this->categories('Root 2')->getRight());

    $this->assertEquals(11, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());
  }

  public function testMakeLastChildOfSwappingRootsWithSubtrees() {
    $newRoot = Category::create(array('name' => 'Root 3'));

    $this->categories('Root 1')->makeLastChildOf($newRoot);

    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentId());

    $this->assertEquals(4, $this->categories('Root 1')->getLeft());
    $this->assertEquals(13, $this->categories('Root 1')->getRight());

    $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
    $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
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

}
