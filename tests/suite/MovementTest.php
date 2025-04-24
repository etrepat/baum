<?php

namespace Baum\Tests;

use Baum\NestedSet\MoveNotPossibleException;

use Baum\Tests\Support\Models\Category;
use Baum\Tests\Support\Models\Cluster;

class MovementTest extends TestCase
{
    public function testMoveLeft()
    {
        $this->categories('Child 2')->moveLeft();

        $this->assertNull($this->categories('Child 2')->getLeftSibling());

        $this->assertEquals($this->categories('Child 1'), $this->categories('Child 2')->getRightSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMoveLeftRaisesAnExceptionWhenNotPossible()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->categories('Child 2');

        $node->moveLeft();
        $node->moveLeft();
    }

    public function testMoveLeftDoesNotChangeDepth()
    {
        $this->categories('Child 2')->moveLeft();

        $this->assertEquals(1, $this->categories('Child 2')->getDepth());
        $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
    }

    public function testMoveLeftWithSubtree()
    {
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

    public function testMoveToLeftOf()
    {
        $this->categories('Child 3')->moveToLeftOf($this->categories('Child 1'));

        $this->assertNull($this->categories('Child 3')->getLeftSibling());

        $this->assertEquals($this->categories('Child 1'), $this->categories('Child 3')->getRightSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMoveToLeftOfRaisesAnExceptionWhenNotPossible()
    {
        $this->expectException(MoveNotPossibleException::class);

        $this->categories('Child 1')->moveToLeftOf($this->categories('Child 1')->getLeftSibling());
    }

    public function testMoveToLeftOfDoesNotChangeDepth()
    {
        $this->categories('Child 2')->moveToLeftOf($this->categories('Child 1'));

        $this->assertEquals(1, $this->categories('Child 2')->getDepth());
        $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
    }

    public function testMoveToLeftOfWithSubtree()
    {
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

    public function testMoveRight()
    {
        $this->categories('Child 2')->moveRight();

        $this->assertNull($this->categories('Child 2')->getRightSibling());

        $this->assertEquals($this->categories('Child 3'), $this->categories('Child 2')->getLeftSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMoveRightRaisesAnExceptionWhenNotPossible()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->categories('Child 2');

        $node->moveRight();
        $node->moveRight();
    }

    public function testMoveRightDoesNotChangeDepth()
    {
        $this->categories('Child 2')->moveRight();

        $this->assertEquals(1, $this->categories('Child 2')->getDepth());
        $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
    }

    public function testMoveRightWithSubtree()
    {
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

    public function testMoveToRightOf()
    {
        $this->categories('Child 1')->moveToRightOf($this->categories('Child 3'));

        $this->assertNull($this->categories('Child 1')->getRightSibling());

        $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->getLeftSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMoveToRightOfRaisesAnExceptionWhenNotPossible()
    {
        $this->expectException(MoveNotPossibleException::class);

        $this->categories('Child 3')->moveToRightOf($this->categories('Child 3')->getRightSibling());
    }

    public function testMoveToRightOfDoesNotChangeDepth()
    {
        $this->categories('Child 2')->moveToRightOf($this->categories('Child 3'));

        $this->assertEquals(1, $this->categories('Child 2')->getDepth());
        $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
    }

    public function testMoveToRightOfWithSubtree()
    {
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

    public function testMakeRoot()
    {
        $this->categories('Child 2')->makeRoot();

        $newRoot = $this->categories('Child 2');

        $this->assertNull($newRoot->parent()->first());
        $this->assertEquals(0, $newRoot->getLevel());
        $this->assertEquals(9, $newRoot->getLeft());
        $this->assertEquals(12, $newRoot->getRight());

        $this->assertEquals(1, $this->categories('Child 2.1')->getLevel());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNullifyParentColumnMakesItRoot()
    {
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

    public function testNullifyParentColumnOnNewNodes()
    {
        $node = new Category(['name' => 'Root 3']);

        $node->parent_id = null;

        $node->save();

        $node->refresh();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(13, $node->getLeft());
        $this->assertEquals(14, $node->getRight());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNewCategoryWithNullParent()
    {
        $node = new Category(['name' => 'Root 3']);
        $this->assertTrue($node->isRoot());

        $node->save();
        $this->assertTrue($node->isRoot());

        $node->makeRoot();
        $this->assertTrue($node->isRoot());
    }

    public function testMakeChildOf()
    {
        $this->categories('Child 1')->makeChildOf($this->categories('Child 3'));

        $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeChildOfAppendsAtTheEnd()
    {
        $newChild = Category::create(['name' => 'Child 4']);

        $newChild->makeChildOf($this->categories('Root 1'));

        $lastChild = $this->categories('Root 1')->children()->get()->last();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeChildOfMovesWithSubtree()
    {
        $this->categories('Child 2')->makeChildOf($this->categories('Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentKey());

        $this->assertEquals(3, $this->categories('Child 2')->getLeft());
        $this->assertEquals(6, $this->categories('Child 2')->getRight());

        $this->assertEquals(2, $this->categories('Child 1')->getLeft());
        $this->assertEquals(7, $this->categories('Child 1')->getRight());
    }

    public function testMakeChildOfSwappingRoots()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->categories('Root 2')->makeChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentKey());

        $this->assertEquals(12, $this->categories('Root 2')->getLeft());
        $this->assertEquals(13, $this->categories('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('Root 1')->makeChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentKey());

        $this->assertEquals(4, $this->categories('Root 1')->getLeft());
        $this->assertEquals(13, $this->categories('Root 1')->getRight());

        $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
    }

    public function testMakeFirstChildOf()
    {
        $this->categories('Child 1')->makeFirstChildOf($this->categories('Child 3'));

        $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeFirstChildOfAppendsAtTheBeginning()
    {
        $newChild = Category::create(['name' => 'Child 4']);

        $newChild->makeFirstChildOf($this->categories('Root 1'));

        $lastChild = $this->categories('Root 1')->children()->get()->first();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeFirstChildOfMovesWithSubtree()
    {
        $this->categories('Child 2')->makeFirstChildOf($this->categories('Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentKey());

        $this->assertEquals(3, $this->categories('Child 2')->getLeft());
        $this->assertEquals(6, $this->categories('Child 2')->getRight());

        $this->assertEquals(2, $this->categories('Child 1')->getLeft());
        $this->assertEquals(7, $this->categories('Child 1')->getRight());
    }

    public function testMakeFirstChildOfSwappingRoots()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->categories('Root 2')->makeFirstChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentKey());

        $this->assertEquals(12, $this->categories('Root 2')->getLeft());
        $this->assertEquals(13, $this->categories('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeFirstChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('Root 1')->makeFirstChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentKey());

        $this->assertEquals(4, $this->categories('Root 1')->getLeft());
        $this->assertEquals(13, $this->categories('Root 1')->getRight());

        $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
    }

    public function testMakeLastChildOf()
    {
        $this->categories('Child 1')->makeLastChildOf($this->categories('Child 3'));

        $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeLastChildOfAppendsAtTheEnd()
    {
        $newChild = Category::create(['name' => 'Child 4']);

        $newChild->makeLastChildOf($this->categories('Root 1'));

        $lastChild = $this->categories('Root 1')->children()->get()->last();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeLastChildOfMovesWithSubtree()
    {
        $this->categories('Child 2')->makeLastChildOf($this->categories('Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentKey());

        $this->assertEquals(3, $this->categories('Child 2')->getLeft());
        $this->assertEquals(6, $this->categories('Child 2')->getRight());

        $this->assertEquals(2, $this->categories('Child 1')->getLeft());
        $this->assertEquals(7, $this->categories('Child 1')->getRight());
    }

    public function testMakeLastChildOfSwappingRoots()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->categories('Root 2')->makeLastChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentKey());

        $this->assertEquals(12, $this->categories('Root 2')->getLeft());
        $this->assertEquals(13, $this->categories('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeLastChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('Root 1')->makeLastChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentKey());

        $this->assertEquals(4, $this->categories('Root 1')->getLeft());
        $this->assertEquals(13, $this->categories('Root 1')->getRight());

        $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
    }

    public function testUnpersistedNodeCannotBeMoved()
    {
        $this->expectException(MoveNotPossibleException::class);

        $unpersisted = new Category(['name' => 'Unpersisted']);

        $unpersisted->moveToRightOf($this->categories('Root 1'));
    }

    public function testUnpersistedNodeCannotBeMadeChild()
    {
        $this->expectException(MoveNotPossibleException::class);

        $unpersisted = new Category(['name' => 'Unpersisted']);

        $unpersisted->makeChildOf($this->categories('Root 1'));
    }

    public function testNodesCannotBeMovedToItself()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->categories('Child 1');

        $node->moveToRightOf($node);
    }

    public function testNodesCannotBeMadeChildOfThemselves()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->categories('Child 1');

        $node->makeChildOf($node);
    }

    public function testNodesCannotBeMovedToDescendantsOfThemselves()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->categories('Root 1');

        $node->makeChildOf($this->categories('Child 2.1'));
    }

    public function testDepthIsUpdatedWhenMadeChild()
    {
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $c = Category::create(['name' => 'C']);
        $d = Category::create(['name' => 'D']);

        // a > b > c > d
        $b->makeChildOf($a);
        $c->makeChildOf($b);
        $d->makeChildOf($c);

        $a->refresh();
        $b->refresh();
        $c->refresh();
        $d->refresh();

        $this->assertEquals(0, $a->getDepth());
        $this->assertEquals(1, $b->getDepth());
        $this->assertEquals(2, $c->getDepth());
        $this->assertEquals(3, $d->getDepth());
    }

    public function testDepthIsUpdatedOnDescendantsWhenParentMoves()
    {
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $c = Category::create(['name' => 'C']);
        $d = Category::create(['name' => 'D']);

        // a > b > c > d
        $b->makeChildOf($a);
        $c->makeChildOf($b);
        $d->makeChildOf($c);

        $a->refresh();
        $b->refresh();
        $c->refresh();
        $d->refresh();

        $b->moveToRightOf($a);

        $a->refresh();
        $b->refresh();
        $c->refresh();
        $d->refresh();

        $this->assertEquals(0, $b->getDepth());
        $this->assertEquals(1, $c->getDepth());
        $this->assertEquals(2, $d->getDepth());
    }

    public function testNonNumericMoveLeft()
    {
        $this->clusters('Child 2')->moveLeft();

        $this->assertNull($this->clusters('Child 2')->getLeftSibling());

        $this->assertEquals($this->clusters('Child 1'), $this->clusters('Child 2')->getRightSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMoveLeftRaisesAnExceptionWhenNotPossible()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->clusters('Child 2');

        $node->moveLeft();
        $node->moveLeft();
    }

    public function testNonNumericMoveLeftDoesNotChangeDepth()
    {
        $this->clusters('Child 2')->moveLeft();

        $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
        $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
    }

    public function testNonNumericMoveLeftWithSubtree()
    {
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

    public function testNonNumericMoveToLeftOf()
    {
        $this->clusters('Child 3')->moveToLeftOf($this->clusters('Child 1'));

        $this->assertNull($this->clusters('Child 3')->getLeftSibling());

        $this->assertEquals($this->clusters('Child 1'), $this->clusters('Child 3')->getRightSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMoveToLeftOfRaisesAnExceptionWhenNotPossible()
    {
        $this->expectException(MoveNotPossibleException::class);

        $this->clusters('Child 1')->moveToLeftOf($this->clusters('Child 1')->getLeftSibling());
    }

    public function testNonNumericMoveToLeftOfDoesNotChangeDepth()
    {
        $this->clusters('Child 2')->moveToLeftOf($this->clusters('Child 1'));

        $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
        $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
    }

    public function testNonNumericMoveToLeftOfWithSubtree()
    {
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

    public function testNonNumericMoveRight()
    {
        $this->clusters('Child 2')->moveRight();

        $this->assertNull($this->clusters('Child 2')->getRightSibling());

        $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 2')->getLeftSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMoveRightRaisesAnExceptionWhenNotPossible()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->clusters('Child 2');

        $node->moveRight();
        $node->moveRight();
    }

    public function testNonNumericMoveRightDoesNotChangeDepth()
    {
        $this->clusters('Child 2')->moveRight();

        $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
        $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
    }

    public function testNonNumericMoveRightWithSubtree()
    {
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

    public function testNonNumericMoveToRightOf()
    {
        $this->clusters('Child 1')->moveToRightOf($this->clusters('Child 3'));

        $this->assertNull($this->clusters('Child 1')->getRightSibling());

        $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 1')->getLeftSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMoveToRightOfRaisesAnExceptionWhenNotPossible()
    {
        $this->expectException(MoveNotPossibleException::class);

        $this->clusters('Child 3')->moveToRightOf($this->clusters('Child 3')->getRightSibling());
    }

    public function testNonNumericMoveToRightOfDoesNotChangeDepth()
    {
        $this->clusters('Child 2')->moveToRightOf($this->clusters('Child 3'));

        $this->assertEquals(1, $this->clusters('Child 2')->getDepth());
        $this->assertEquals(2, $this->clusters('Child 2.1')->getDepth());
    }

    public function testNonNumericMoveToRightOfWithSubtree()
    {
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

    public function testNonNumericMakeRoot()
    {
        $this->clusters('Child 2')->makeRoot();

        $newRoot = $this->clusters('Child 2');

        $this->assertNull($newRoot->parent()->first());
        $this->assertEquals(0, $newRoot->getLevel());
        $this->assertEquals(9, $newRoot->getLeft());
        $this->assertEquals(12, $newRoot->getRight());

        $this->assertEquals(1, $this->clusters('Child 2.1')->getLevel());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericNullifyParentColumnMakesItRoot()
    {
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

    public function testNonNumericNullifyParentColumnOnNewNodes()
    {
        $node = new Cluster(['name' => 'Root 3']);

        $node->parent_id = null;

        $node->save();

        $node->refresh();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(13, $node->getLeft());
        $this->assertEquals(14, $node->getRight());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericNewClusterWithNullParent()
    {
        $node = new Cluster(['name' => 'Root 3']);
        $this->assertTrue($node->isRoot());

        $node->save();
        $this->assertTrue($node->isRoot());

        $node->makeRoot();
        $this->assertTrue($node->isRoot());
    }

    public function testNonNumericMakeChildOf()
    {
        $this->clusters('Child 1')->makeChildOf($this->clusters('Child 3'));

        $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMakeChildOfAppendsAtTheEnd()
    {
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeChildOf($this->clusters('Root 1'));

        $lastChild = $this->clusters('Root 1')->children()->get()->last();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMakeChildOfMovesWithSubtree()
    {
        $this->clusters('Child 2')->makeChildOf($this->clusters('Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($this->clusters('Child 1')->getKey(), $this->clusters('Child 2')->getParentKey());

        $this->assertEquals(3, $this->clusters('Child 2')->getLeft());
        $this->assertEquals(6, $this->clusters('Child 2')->getRight());

        $this->assertEquals(2, $this->clusters('Child 1')->getLeft());
        $this->assertEquals(7, $this->clusters('Child 1')->getRight());
    }

    public function testNonNumericMakeChildOfSwappingRoots()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->clusters('Root 2')->makeChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->clusters('Root 2')->getParentKey());

        $this->assertEquals(12, $this->clusters('Root 2')->getLeft());
        $this->assertEquals(13, $this->clusters('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testNonNumericMakeChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->clusters('Root 1')->makeChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->clusters('Root 1')->getParentKey());

        $this->assertEquals(4, $this->clusters('Root 1')->getLeft());
        $this->assertEquals(13, $this->clusters('Root 1')->getRight());

        $this->assertEquals(8, $this->clusters('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->clusters('Child 2.1')->getRight());
    }

    public function testNonNumericMakeFirstChildOf()
    {
        $this->clusters('Child 1')->makeFirstChildOf($this->clusters('Child 3'));

        $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMakeFirstChildOfAppendsAtTheBeginning()
    {
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeFirstChildOf($this->clusters('Root 1'));

        $lastChild = $this->clusters('Root 1')->children()->get()->first();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMakeFirstChildOfMovesWithSubtree()
    {
        $this->clusters('Child 2')->makeFirstChildOf($this->clusters('Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($this->clusters('Child 1')->getKey(), $this->clusters('Child 2')->getParentKey());

        $this->assertEquals(3, $this->clusters('Child 2')->getLeft());
        $this->assertEquals(6, $this->clusters('Child 2')->getRight());

        $this->assertEquals(2, $this->clusters('Child 1')->getLeft());
        $this->assertEquals(7, $this->clusters('Child 1')->getRight());
    }

    public function testNonNumericMakeFirstChildOfSwappingRoots()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->clusters('Root 2')->makeFirstChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->clusters('Root 2')->getParentKey());

        $this->assertEquals(12, $this->clusters('Root 2')->getLeft());
        $this->assertEquals(13, $this->clusters('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testNonNumericMakeFirstChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->clusters('Root 1')->makeFirstChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->clusters('Root 1')->getParentKey());

        $this->assertEquals(4, $this->clusters('Root 1')->getLeft());
        $this->assertEquals(13, $this->clusters('Root 1')->getRight());

        $this->assertEquals(8, $this->clusters('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->clusters('Child 2.1')->getRight());
    }

    public function testNonNumericMakeLastChildOf()
    {
        $this->clusters('Child 1')->makeLastChildOf($this->clusters('Child 3'));

        $this->assertEquals($this->clusters('Child 3'), $this->clusters('Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMakeLastChildOfAppendsAtTheEnd()
    {
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeLastChildOf($this->clusters('Root 1'));

        $lastChild = $this->clusters('Root 1')->children()->get()->last();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNonNumericMakeLastChildOfMovesWithSubtree()
    {
        $this->clusters('Child 2')->makeLastChildOf($this->clusters('Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($this->clusters('Child 1')->getKey(), $this->clusters('Child 2')->getParentKey());

        $this->assertEquals(3, $this->clusters('Child 2')->getLeft());
        $this->assertEquals(6, $this->clusters('Child 2')->getRight());

        $this->assertEquals(2, $this->clusters('Child 1')->getLeft());
        $this->assertEquals(7, $this->clusters('Child 1')->getRight());
    }

    public function testNonNumericMakeLastChildOfSwappingRoots()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->clusters('Root 2')->makeLastChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->clusters('Root 2')->getParentKey());

        $this->assertEquals(12, $this->clusters('Root 2')->getLeft());
        $this->assertEquals(13, $this->clusters('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testNonNumericMakeLastChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->clusters('Root 1')->makeLastChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->clusters('Root 1')->getParentKey());

        $this->assertEquals(4, $this->clusters('Root 1')->getLeft());
        $this->assertEquals(13, $this->clusters('Root 1')->getRight());

        $this->assertEquals(8, $this->clusters('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->clusters('Child 2.1')->getRight());
    }

    public function testNonNumericUnpersistedNodeCannotBeMoved()
    {
        $this->expectException(MoveNotPossibleException::class);

        $unpersisted = new Cluster(['name' => 'Unpersisted']);

        $unpersisted->moveToRightOf($this->clusters('Root 1'));
    }

    public function testNonNumericUnpersistedNodeCannotBeMadeChild()
    {
        $this->expectException(MoveNotPossibleException::class);

        $unpersisted = new Cluster(['name' => 'Unpersisted']);

        $unpersisted->makeChildOf($this->clusters('Root 1'));
    }

    public function testNonNumericNodesCannotBeMovedToItself()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->clusters('Child 1');

        $node->moveToRightOf($node);
    }

    public function testNonNumericNodesCannotBeMadeChildOfThemselves()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->clusters('Child 1');

        $node->makeChildOf($node);
    }

    public function testNonNumericNodesCannotBeMovedToDescendantsOfThemselves()
    {
        $this->expectException(MoveNotPossibleException::class);

        $node = $this->clusters('Root 1');

        $node->makeChildOf($this->clusters('Child 2.1'));
    }

    public function testNonNumericDepthIsUpdatedWhenMadeChild()
    {
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);
        $d = Cluster::create(['name' => 'D']);

        // a > b > c > d
        $b->makeChildOf($a);
        $c->makeChildOf($b);
        $d->makeChildOf($c);

        $a->refresh();
        $b->refresh();
        $c->refresh();
        $d->refresh();

        $this->assertEquals(0, $a->getDepth());
        $this->assertEquals(1, $b->getDepth());
        $this->assertEquals(2, $c->getDepth());
        $this->assertEquals(3, $d->getDepth());
    }

    public function testNonNumericDepthIsUpdatedOnDescendantsWhenParentMoves()
    {
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);
        $d = Cluster::create(['name' => 'D']);

        // a > b > c > d
        $b->makeChildOf($a);
        $c->makeChildOf($b);
        $d->makeChildOf($c);

        $a->refresh();
        $b->refresh();
        $c->refresh();
        $d->refresh();

        $b->moveToRightOf($a);

        $a->refresh();
        $b->refresh();
        $c->refresh();
        $d->refresh();

        $this->assertEquals(0, $b->getDepth());
        $this->assertEquals(1, $c->getDepth());
        $this->assertEquals(2, $d->getDepth());
    }
}
