<?php

namespace Baum\Tests;

use Baum\Tests\Support\Models\Category;

class TreeValidationTest extends TestCase
{
    public function testTreeIsNotValidWithNullLefts()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->update(['left' => null]);
        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWithNullRights()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->update(['right' => null]);
        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWhenRightsEqualLefts()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = $this->categories('Child 2');
        $child2->right = $child2->left;
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWhenLeftEqualsParent()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = $this->categories('Child 2');
        $child2->left = $this->categories('Root 1')->getLeft();
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWhenRightEqualsParent()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = $this->categories('Child 2');
        $child2->right = $this->categories('Root 1')->getRight();
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsValidWithMissingMiddleNode()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->delete($this->categories('Child 2')->getKey());
        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWithOverlappingRoots()
    {
        $this->assertTrue(Category::isValidNestedSet());

        // Force Root 2 to overlap with Root 1
        $root = $this->categories('Root 2');
        $root->left = 0;
        $root->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testNodeDeletionDoesNotMakeTreeInvalid()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $this->categories('Root 2')->delete();
        $this->assertTrue(Category::isValidNestedSet());

        $this->categories('Child 1')->delete();
        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNodeDeletionWithSubtreeDoesNotMakeTreeInvalid()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $this->categories('Child 2')->delete();
        $this->assertTrue(Category::isValidNestedSet());
    }
}
