<?php

class CategoryTreeValidationTest extends CategoryTestCase {

  public function testTreeIsNotValidWithNullLefts() {
    $this->assertTrue(Category::isValid());

    Category::query()->update(array('lft' => null));
    $this->assertFalse(Category::isValid());
  }

  public function testTreeIsNotValidWithNullRights() {
    $this->assertTrue(Category::isValid());

    Category::query()->update(array('rgt' => null));
    $this->assertFalse(Category::isValid());
  }

  public function testTreeIsNotValidWhenRightsEqualLefts() {
    $this->assertTrue(Category::isValid());

    $child2 = $this->categories('Child 2');
    $child2->rgt = $child2->lft;
    $child2->save();

    $this->assertFalse(Category::isValid());
  }

  public function testTreeIsNotValidWhenLeftEqualsParent() {
    $this->assertTrue(Category::isValid());

    $child2 = $this->categories('Child 2');
    $child2->lft = $this->categories('Root 1')->getLeft();
    $child2->save();

    $this->assertFalse(Category::isValid());
  }

  public function testTreeIsNotValidWhenRightEqualsParent() {
    $this->assertTrue(Category::isValid());

    $child2 = $this->categories('Child 2');
    $child2->rgt = $this->categories('Root 1')->getRight();
    $child2->save();

    $this->assertFalse(Category::isValid());
  }

  public function testTreeIsValidWithMissingMiddleNode() {
    $this->assertTrue(Category::isValid());

    Category::query()->delete($this->categories('Child 2')->getKey());
    $this->assertTrue(Category::isValid());
  }

  public function testTreeIsNotValidWithOverlappingRoots() {
    $this->assertTrue(Category::isValid());

    // Force Root 2 to overlap with Root 1
    $root = $this->categories('Root 2');
    $root->lft = 0;
    $root->save();

    $this->assertFalse(Category::isValid());
  }

  public function testNodeDeletionDoesNotMakeTreeInvalid() {
    $this->assertTrue(Category::isValid());

    $this->categories('Root 2')->delete();
    $this->assertTrue(Category::isValid());

    $this->categories('Child 1')->delete();
    $this->assertTrue(Category::isValid());
  }

  public function testNodeDeletionWithSubtreeDoesNotMakeTreeInvalid() {
    $this->assertTrue(Category::isValid());

    $this->categories('Child 2')->delete();
    $this->assertTrue(Category::isValid());
  }

}
