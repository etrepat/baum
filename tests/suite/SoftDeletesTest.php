<?php

namespace Baum\Tests;

use Baum\Tests\Support\Models\Category;
use Baum\Tests\Support\Models\SoftCategory;

class SoftDeletesTest extends TestCase
{
    public function testHasSoftDeletes()
    {
        $this->assertFalse(with(new Category)->hasSoftDeletes());
        $this->assertTrue(with(new SoftCategory())->hasSoftDeletes());
    }

    public function testReload()
    {
        $node = $this->categories('Child 3', SoftCategory::class);

        $this->assertTrue($node->exists);
        $this->assertFalse($node->trashed());

        $node->delete();

        $this->assertTrue($node->trashed());

        $node->refresh();

        $this->assertTrue($node->trashed());
        $this->assertTrue($node->exists);
    }

    public function testDeleteMaintainsTreeValid()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child3 = $this->categories('Child 3', SoftCategory::class);
        $child3->delete();

        $this->assertTrue($child3->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());
    }

    public function testDeleteMaintainsTreeValidWithSubtrees()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2 = $this->categories('Child 2', SoftCategory::class);
        $child2->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        ];
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());
    }

    public function testDeleteShiftsIndexes()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Child 1', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        ];
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(8, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 2', SoftCategory::class)->getLeft());
        $this->assertEquals(5, $this->categories('Child 2', SoftCategory::class)->getRight());

        $this->assertEquals(3, $this->categories('Child 2.1', SoftCategory::class)->getLeft());
        $this->assertEquals(4, $this->categories('Child 2.1', SoftCategory::class)->getRight());

        $this->assertEquals(6, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(7, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testDeleteShiftsIndexesSubtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Child 2', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        ];
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(6, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 1', SoftCategory::class)->getLeft());
        $this->assertEquals(3, $this->categories('Child 1', SoftCategory::class)->getRight());

        $this->assertEquals(4, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(5, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testDeleteShiftsIndexesFullSubtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Root 1', SoftCategory::class)->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->assertEmpty($this->categories('Root 2', SoftCategory::class)->getSiblings()->all());
        $this->assertEquals(1, $this->categories('Root 2', SoftCategory::class)->getLeft());
        $this->assertEquals(2, $this->categories('Root 2', SoftCategory::class)->getRight());
    }

    public function testRestoreMaintainsTreeValid()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child3 = $this->categories('Child 3', SoftCategory::class);
        $child3->delete();

        $this->assertTrue($child3->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child3->refresh();
        $child3->restore();

        $this->assertFalse($child3->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());
    }

    public function testRestoreMaintainsTreeValidWithSubtrees()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2 = $this->categories('Child 2', SoftCategory::class);
        $child2->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2->refresh();
        $child2->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        ];
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());
    }

    public function testRestoreUnshiftsIndexes()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Child 1', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::withTrashed()->where('name', 'Child 1')->first()->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        ];
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(10, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 1', SoftCategory::class)->getLeft());
        $this->assertEquals(3, $this->categories('Child 1', SoftCategory::class)->getRight());

        $this->assertEquals(4, $this->categories('Child 2', SoftCategory::class)->getLeft());
        $this->assertEquals(7, $this->categories('Child 2', SoftCategory::class)->getRight());
        $this->assertEquals(5, $this->categories('Child 2.1', SoftCategory::class)->getLeft());
        $this->assertEquals(6, $this->categories('Child 2.1', SoftCategory::class)->getRight());

        $this->assertEquals(8, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(9, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testRestoreUnshiftsIndexesSubtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Child 2', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::withTrashed()->where('name', 'Child 2')->first()->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        ];
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(10, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 1', SoftCategory::class)->getLeft());
        $this->assertEquals(3, $this->categories('Child 1', SoftCategory::class)->getRight());

        $this->assertEquals(4, $this->categories('Child 2', SoftCategory::class)->getLeft());
        $this->assertEquals(7, $this->categories('Child 2', SoftCategory::class)->getRight());
        $this->assertEquals(5, $this->categories('Child 2.1', SoftCategory::class)->getLeft());
        $this->assertEquals(6, $this->categories('Child 2.1', SoftCategory::class)->getRight());

        $this->assertEquals(8, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(9, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testRestoreUnshiftsIndexesFullSubtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Root 1', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::withTrashed()->where('name', 'Root 1')->first()->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        ];
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(10, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 1', SoftCategory::class)->getLeft());
        $this->assertEquals(3, $this->categories('Child 1', SoftCategory::class)->getRight());

        $this->assertEquals(4, $this->categories('Child 2', SoftCategory::class)->getLeft());
        $this->assertEquals(7, $this->categories('Child 2', SoftCategory::class)->getRight());
        $this->assertEquals(5, $this->categories('Child 2.1', SoftCategory::class)->getLeft());
        $this->assertEquals(6, $this->categories('Child 2.1', SoftCategory::class)->getRight());

        $this->assertEquals(8, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(9, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testAllStatic()
    {
        $expected = ['Root 1', 'Child 1', 'Child 2', 'Child 2.1', 'Child 3', 'Root 2'];

        $this->assertEquals($expected, SoftCategory::all()->pluck('name')->all());
    }

    public function testAllStaticWithSoftDeletes()
    {
        $this->categories('Child 1', SoftCategory::class)->delete();
        $this->categories('Child 3', SoftCategory::class)->delete();

        $expected = ['Root 1', 'Child 2', 'Child 2.1', 'Root 2'];
        $this->assertEquals($expected, SoftCategory::all()->pluck('name')->all());
    }
}
