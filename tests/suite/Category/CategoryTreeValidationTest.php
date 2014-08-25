<?php

class CategoryTreeValidationTest extends CategoryTestCase {

  public function testTreeIsNotValidWithNullLefts() {
    $this->assertTrue(Category::isValidNestedSet());

    Category::query()->update(array('lft' => null));
    $this->assertFalse(Category::isValidNestedSet());
  }

  public function testTreeIsNotValidWithNullRights() {
    $this->assertTrue(Category::isValidNestedSet());

    Category::query()->update(array('rgt' => null));
    $this->assertFalse(Category::isValidNestedSet());
  }

  public function testTreeIsNotValidWhenRightsEqualLefts() {
    $this->assertTrue(Category::isValidNestedSet());

    $child2 = $this->categories('Child 2');
    $child2->rgt = $child2->lft;
    $child2->save();

    $this->assertFalse(Category::isValidNestedSet());
  }

  public function testTreeIsNotValidWhenLeftEqualsParent() {
    $this->assertTrue(Category::isValidNestedSet());

    $child2 = $this->categories('Child 2');
    $child2->lft = $this->categories('Root 1')->getLeft();
    $child2->save();

    $this->assertFalse(Category::isValidNestedSet());
  }

  public function testTreeIsNotValidWhenRightEqualsParent() {
    $this->assertTrue(Category::isValidNestedSet());

    $child2 = $this->categories('Child 2');
    $child2->rgt = $this->categories('Root 1')->getRight();
    $child2->save();

    $this->assertFalse(Category::isValidNestedSet());
  }

  public function testTreeIsValidWithMissingMiddleNode() {
    $this->assertTrue(Category::isValidNestedSet());

    Category::query()->delete($this->categories('Child 2')->getKey());
    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testTreeIsNotValidWithOverlappingRoots() {
    $this->assertTrue(Category::isValidNestedSet());

    // Force Root 2 to overlap with Root 1
    $root = $this->categories('Root 2');
    $root->lft = 0;
    $root->save();

    $this->assertFalse(Category::isValidNestedSet());
  }

  public function testNodeDeletionDoesNotMakeTreeInvalid() {
    $this->assertTrue(Category::isValidNestedSet());

    $this->categories('Root 2')->delete();
    $this->assertTrue(Category::isValidNestedSet());

    $this->categories('Child 1')->delete();
    $this->assertTrue(Category::isValidNestedSet());
  }

  public function testNodeDeletionWithSubtreeDoesNotMakeTreeInvalid() {
    $this->assertTrue(Category::isValidNestedSet());

    $this->categories('Child 2')->delete();
    $this->assertTrue(Category::isValidNestedSet());
  }

}
