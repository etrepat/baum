<?php

class ClusterMovementTest extends ClusterTestCase {

  public function testMoveLeft() {
    $this->clusters('Child 2')->moveLeft();

    $this->assertNull($this->clusters('Child 2')->getLeftSibling());

    $this->assertEquals($this->clusters('Child 1'), $this->clusters('Child 2')->getRightSibling());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveLeftRaisesAnExceptionWhenNotPossible() {
    $node = $this->clusters('Child 2');

    $node->moveLeft();
    $node->moveLeft();
  }

  public function testMoveLeftDoesNotChangeDepth() {
    $this->clusters('Child 2')->moveLeft();

    $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
    $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
  }

  public function testMoveLeftWithSubtree() {
    $this->clusters('Root 2')->moveLeft();

    $this->assertNull($this->clusters('Root 2')->getLeftSibling());
    $this->assertEquals($this->clusters('Root 1'), $this->clusters('Root 2')->getRightSibling());
    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals(0, $this->clusters('Root 1')->getDepth());
    $this->assertEquals(0, $this->clusters('Root 2')->getDepth());

    $this->assertEquals(1, $this->clusters('Child 1')->getDepth());
    $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
    $this->assertEquals(1, $this->clusters('Child 3')->getDepth());

    $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
  }

  public function testMoveToLeftOf() {
    $this->clusters('Child 3')->moveToLeftOf($this->clusters('Child 1'));

    $this->assertNull($this->clusters('Child 3')->getLeftSibling());

    $this->assertEquals($this->clusters('Child 1'), $this->clusters('Child 3')->getRightSibling());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveToLeftOfRaisesAnExceptionWhenNotPossible() {
    $this->clusters('Child 1')->moveToLeftOf($this->clusters('Child 1')->getLeftSibling());
  }

  public function testMoveToLeftOfDoesNotChangeDepth() {
    $this->clusters('Child 2')->moveToLeftOf($this->clusters('Child 1'));

    $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
    $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
  }

  public function testMoveToLeftOfWithSubtree() {
    $this->clusters('Root 2')->moveToLeftOf($this->clusters('Root 1'));

    $this->assertNull($this->clusters('Root 2')->getLeftSibling());
    $this->assertEquals($this->clusters('Root 1'), $this->clusters('Root 2')->getRightSibling());
    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals(0, $this->clusters('Root 1')->getDepth());
    $this->assertEquals(0, $this->clusters('Root 2')->getDepth());

    $this->assertEquals(1, $this->clusters('Child 1')->getDepth());
    $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
    $this->assertEquals(1, $this->clusters('Child 3')->getDepth());

    $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
  }

  public function testMoveRight() {
    $this->clusters('Child 2')->moveRight();

    $this->assertNull($this->clusters('Child 2')->getRightSibling());

    $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 2')->getLeftSibling());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveRightRaisesAnExceptionWhenNotPossible() {
    $node = $this->clusters('Child 2');

    $node->moveRight();
    $node->moveRight();
  }

  public function testMoveRightDoesNotChangeDepth() {
    $this->clusters('Child 2')->moveRight();

    $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
    $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
  }

  public function testMoveRightWithSubtree() {
    $this->clusters('Root 1')->moveRight();

    $this->assertNull($this->clusters('Root 1')->getRightSibling());
    $this->assertEquals($this->clusters('Root 2'), $this->clusters('Root 1')->getLeftSibling());
    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals(0, $this->clusters('Root 1')->getDepth());
    $this->assertEquals(0, $this->clusters('Root 2')->getDepth());

    $this->assertEquals(1, $this->clusters('Child 1')->getDepth());
    $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
    $this->assertEquals(1, $this->clusters('Child 3')->getDepth());

    $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
  }

  public function testMoveToRightOf() {
    $this->clusters('Child 1')->moveToRightOf($this->clusters('Child 3'));

    $this->assertNull($this->clusters('Child 1')->getRightSibling());

    $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 1')->getLeftSibling());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testMoveToRightOfRaisesAnExceptionWhenNotPossible() {
    $this->clusters('Child 3')->moveToRightOf($this->clusters('Child 3')->getRightSibling());
  }

  public function testMoveToRightOfDoesNotChangeDepth() {
    $this->clusters('Child 2')->moveToRightOf($this->clusters('Child 3'));

    $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
    $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
  }

  public function testMoveToRightOfWithSubtree() {
    $this->clusters('Root 1')->moveToRightOf($this->clusters('Root 2'));

    $this->assertNull($this->clusters('Root 1')->getRightSibling());
    $this->assertEquals($this->clusters('Root 2'), $this->clusters('Root 1')->getLeftSibling());
    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals(0, $this->clusters('Root 1')->getDepth());
    $this->assertEquals(0, $this->clusters('Root 2')->getDepth());

    $this->assertEquals(1, $this->clusters('Child 1')->getDepth());
    $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
    $this->assertEquals(1, $this->clusters('Child 3')->getDepth());

    $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
  }

  public function testMakeRoot() {
    $this->clusters('Child 2')->makeRoot();

    $newRoot = $this->clusters('Child 2');

    $this->assertNull($newRoot->parent()->first());
    $this->assertEquals(0, $newRoot->getLevel());
    $this->assertEquals(9, $newRoot->getLeft());
    $this->assertEquals(12, $newRoot->getRight());

    $this->assertEquals(1, $this->clusters('Child 2.1')->getLevel());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testNullifyParentColumnMakesItRoot() {
    $node = $this->clusters('Child 2');

    $node->parent_id = null;

    $node->save();

    $this->assertNull($node->parent()->first());
    $this->assertEquals(0, $node->getLevel());
    $this->assertEquals(9, $node->getLeft());
    $this->assertEquals(12, $node->getRight());

    $this->assertEquals(1, $this->clusters('Child 2.1')->getLevel());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testNullifyParentColumnOnNewNodes() {
    $node = new Cluster(['name' => 'Root 3']);

    $node->parent_id = null;

    $node->save();

    $node->reload();

    $this->assertNull($node->parent()->first());
    $this->assertEquals(0, $node->getLevel());
    $this->assertEquals(13, $node->getLeft());
    $this->assertEquals(14, $node->getRight());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testNewClusterWithNullParent() {
    $node = new Cluster(['name' => 'Root 3']);
    $this->assertTrue($node->isRoot());

    $node->save();
    $this->assertTrue($node->isRoot());

    $node->makeRoot();
    $this->assertTrue($node->isRoot());
  }

  public function testMakeChildOf() {
    $this->clusters('Child 1')->makeChildOf($this->clusters('Child 3'));

    $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 1')->parent()->first());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testMakeChildOfAppendsAtTheEnd() {
    $newChild = Cluster::create(array('name' => 'Child 4'));

    $newChild->makeChildOf($this->clusters('Root 1'));

    $lastChild = $this->clusters('Root 1')->children()->get()->last();
    $this->assertEquals($newChild, $lastChild);

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testMakeChildOfMovesWithSubtree() {
    $this->clusters('Child 2')->makeChildOf($this->clusters('Child 1'));

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($this->clusters('Child 1')->getKey(), $this->clusters('Child 2')->getParentId());

    $this->assertEquals(3, $this->clusters('Child 2')->getLeft());
    $this->assertEquals(6, $this->clusters('Child 2')->getRight());

    $this->assertEquals(2, $this->clusters('Child 1')->getLeft());
    $this->assertEquals(7, $this->clusters('Child 1')->getRight());
  }

  public function testMakeChildOfSwappingRoots() {
    $newRoot = Cluster::create(array('name' => 'Root 3'));

    $this->assertEquals(13, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());

    $this->clusters('Root 2')->makeChildOf($newRoot);

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->clusters('Root 2')->getParentId());

    $this->assertEquals(12, $this->clusters('Root 2')->getLeft());
    $this->assertEquals(13, $this->clusters('Root 2')->getRight());

    $this->assertEquals(11, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());
  }

  public function testMakeChildOfSwappingRootsWithSubtrees() {
    $newRoot = Cluster::create(array('name' => 'Root 3'));

    $this->clusters('Root 1')->makeChildOf($newRoot);

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->clusters('Root 1')->getParentId());

    $this->assertEquals(4, $this->clusters('Root 1')->getLeft());
    $this->assertEquals(13, $this->clusters('Root 1')->getRight());

    $this->assertEquals(8, $this->clusters('Child 2.1')->getLeft());
    $this->assertEquals(9, $this->clusters('Child 2.1')->getRight());
  }

  public function testMakeFirstChildOf() {
    $this->clusters('Child 1')->makeFirstChildOf($this->clusters('Child 3'));

    $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 1')->parent()->first());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testMakeFirstChildOfAppendsAtTheBeginning() {
    $newChild = Cluster::create(array('name' => 'Child 4'));

    $newChild->makeFirstChildOf($this->clusters('Root 1'));

    $lastChild = $this->clusters('Root 1')->children()->get()->first();
    $this->assertEquals($newChild, $lastChild);

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testMakeFirstChildOfMovesWithSubtree() {
    $this->clusters('Child 2')->makeFirstChildOf($this->clusters('Child 1'));

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($this->clusters('Child 1')->getKey(), $this->clusters('Child 2')->getParentId());

    $this->assertEquals(3, $this->clusters('Child 2')->getLeft());
    $this->assertEquals(6, $this->clusters('Child 2')->getRight());

    $this->assertEquals(2, $this->clusters('Child 1')->getLeft());
    $this->assertEquals(7, $this->clusters('Child 1')->getRight());
  }

  public function testMakeFirstChildOfSwappingRoots() {
    $newRoot = Cluster::create(array('name' => 'Root 3'));

    $this->assertEquals(13, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());

    $this->clusters('Root 2')->makeFirstChildOf($newRoot);

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->clusters('Root 2')->getParentId());

    $this->assertEquals(12, $this->clusters('Root 2')->getLeft());
    $this->assertEquals(13, $this->clusters('Root 2')->getRight());

    $this->assertEquals(11, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());
  }

  public function testMakeFirstChildOfSwappingRootsWithSubtrees() {
    $newRoot = Cluster::create(array('name' => 'Root 3'));

    $this->clusters('Root 1')->makeFirstChildOf($newRoot);

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->clusters('Root 1')->getParentId());

    $this->assertEquals(4, $this->clusters('Root 1')->getLeft());
    $this->assertEquals(13, $this->clusters('Root 1')->getRight());

    $this->assertEquals(8, $this->clusters('Child 2.1')->getLeft());
    $this->assertEquals(9, $this->clusters('Child 2.1')->getRight());
  }

  public function testMakeLastChildOf() {
    $this->clusters('Child 1')->makeLastChildOf($this->clusters('Child 3'));

    $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 1')->parent()->first());

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testMakeLastChildOfAppendsAtTheEnd() {
    $newChild = Cluster::create(array('name' => 'Child 4'));

    $newChild->makeLastChildOf($this->clusters('Root 1'));

    $lastChild = $this->clusters('Root 1')->children()->get()->last();
    $this->assertEquals($newChild, $lastChild);

    $this->assertTrue(Cluster::isValidNestedSet());
  }

  public function testMakeLastChildOfMovesWithSubtree() {
    $this->clusters('Child 2')->makeLastChildOf($this->clusters('Child 1'));

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($this->clusters('Child 1')->getKey(), $this->clusters('Child 2')->getParentId());

    $this->assertEquals(3, $this->clusters('Child 2')->getLeft());
    $this->assertEquals(6, $this->clusters('Child 2')->getRight());

    $this->assertEquals(2, $this->clusters('Child 1')->getLeft());
    $this->assertEquals(7, $this->clusters('Child 1')->getRight());
  }

  public function testMakeLastChildOfSwappingRoots() {
    $newRoot = Cluster::create(array('name' => 'Root 3'));

    $this->assertEquals(13, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());

    $this->clusters('Root 2')->makeLastChildOf($newRoot);

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->clusters('Root 2')->getParentId());

    $this->assertEquals(12, $this->clusters('Root 2')->getLeft());
    $this->assertEquals(13, $this->clusters('Root 2')->getRight());

    $this->assertEquals(11, $newRoot->getLeft());
    $this->assertEquals(14, $newRoot->getRight());
  }

  public function testMakeLastChildOfSwappingRootsWithSubtrees() {
    $newRoot = Cluster::create(array('name' => 'Root 3'));

    $this->clusters('Root 1')->makeLastChildOf($newRoot);

    $this->assertTrue(Cluster::isValidNestedSet());

    $this->assertEquals($newRoot->getKey(), $this->clusters('Root 1')->getParentId());

    $this->assertEquals(4, $this->clusters('Root 1')->getLeft());
    $this->assertEquals(13, $this->clusters('Root 1')->getRight());

    $this->assertEquals(8, $this->clusters('Child 2.1')->getLeft());
    $this->assertEquals(9, $this->clusters('Child 2.1')->getRight());
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testUnpersistedNodeCannotBeMoved() {
    $unpersisted = new Cluster(array('name' => 'Unpersisted'));

    $unpersisted->moveToRightOf($this->clusters('Root 1'));
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testUnpersistedNodeCannotBeMadeChild() {
    $unpersisted = new Cluster(array('name' => 'Unpersisted'));

    $unpersisted->makeChildOf($this->clusters('Root 1'));
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotBeMovedToItself() {
    $node = $this->clusters('Child 1');

    $node->moveToRightOf($node);
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotBeMadeChildOfThemselves() {
    $node = $this->clusters('Child 1');

    $node->makeChildOf($node);
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotBeMovedToDescendantsOfThemselves() {
    $node = $this->clusters('Root 1');

    $node->makeChildOf($this->clusters('Child 2.1'));
  }

  public function testDepthIsUpdatedWhenMadeChild() {
    $a = Cluster::create(array('name' => 'A'));
    $b = Cluster::create(array('name' => 'B'));
    $c = Cluster::create(array('name' => 'C'));
    $d = Cluster::create(array('name' => 'D'));

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
    $a = Cluster::create(array('name' => 'A'));
    $b = Cluster::create(array('name' => 'B'));
    $c = Cluster::create(array('name' => 'C'));
    $d = Cluster::create(array('name' => 'D'));

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
