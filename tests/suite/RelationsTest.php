<?php

namespace Baum\Tests;

use Baum\Tests\Support\Models\Category;
use Baum\Tests\Support\Models\OrderedCategory;
use Baum\Tests\Support\Seeders\OrderedCategorySeeder;

class RelationsTest extends TestCase
{
    public function testParentRelationIsABelongsTo()
    {
        $category = new Category;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $category->parent());
    }

    public function testParentRelationIsSelfReferential()
    {
        $category = new Category;

        $klass = get_class($category);

        $this->assertInstanceOf($klass, $category);
        $this->assertInstanceOf($klass, $category->parent()->getRelated());
        $this->assertEquals($klass, get_class($category->parent()->getRelated()));
    }

    public function testParentRelationRefersToCorrectField()
    {
        $category = new Category;

        if (method_exists($category->parent(), 'getForeignKeyName')) {
            // For Laravel 5.6+
            $this->assertEquals($category->getParentColumnName(), $category->parent()->getForeignKeyName());
            $this->assertEquals($category->getQualifiedParentColumnName(), $category->parent()->getQualifiedForeignKeyName());
        } else {
            $this->assertEquals($category->getParentColumnName(), $category->parent()->getForeignKey());
            $this->assertEquals($category->getQualifiedParentColumnName(), $category->parent()->getQualifiedForeignKey());
        }
    }

    public function testParentRelation()
    {
        $this->assertEquals($this->categories('Child 2.1')->parent()->first(), $this->categories('Child 2'));
        $this->assertEquals($this->categories('Child 2')->parent()->first(), $this->categories('Root 1'));
        $this->assertNull($this->categories('Root 1')->parent()->first());
    }

    public function testChildrenRelationIsAHasMany()
    {
        $category = new Category;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $category->children());
    }

    public function testChildrenRelationIsSelfReferential()
    {
        $category = new Category;

        $klass = get_class($category);

        $this->assertInstanceOf($klass, $category);
        $this->assertInstanceOf($klass, $category->children()->getRelated());
        $this->assertEquals($klass, get_class($category->parent()->getRelated()));
    }

    public function testChildrenRelationRefersToCorrectField()
    {
        $category = new Category;

        $this->assertEquals($category->getParentColumnName(), $category->children()->getForeignKeyName());

        $this->assertEquals($category->getQualifiedParentColumnName(), $category->children()->getQualifiedForeignKeyName());
    }

    public function testChildrenRelation()
    {
        $root = $this->categories('Root 1');

        foreach ($root->children() as $child) {
            $this->assertEquals($root->getKey(), $child->getParentKey());
        }

        $expected = [$this->categories('Child 1'), $this->categories('Child 2'), $this->categories('Child 3')];

        $this->assertEquals($expected, $root->children()->get()->all());

        $this->assertEmpty($this->categories('Child 3')->children()->get()->all());
    }

    public function testChildrenRelationUsesDefaultOrdering()
    {
        $category = new Category;

        $query = $category->children()->getQuery()->toBase();

        $expected = ['column' => $category->qualifyColumn('left'), 'direction' => 'asc'];

        $this->assertEquals($expected, $query->orders[0]);
    }

    public function testChildrenRelationUsesCustomOrdering()
    {
        $category = new OrderedCategory;

        $query = $category->children()->getQuery()->toBase();

        $expected = ['column' => $category->qualifyColumn('name'), 'direction' => 'asc'];

        $this->assertEquals($expected, $query->orders[0]);
    }

    public function testChildrenRelationObeysDefaultOrdering()
    {
        $children = $this->categories('Root 1')->children()->get()->all();

        $expected = [$this->categories('Child 1'), $this->categories('Child 2'), $this->categories('Child 3')];
        $this->assertEquals($expected, $children);

        // Swap 2 nodes & re-test
        Category::query()->where('id', '=', 2)->update(['left' => 8, 'right' => 9]);
        Category::query()->where('id', '=', 5)->update(['left' => 2, 'right' => 3]);

        $children = $this->categories('Root 1')->children()->get()->all();

        $expected = [$this->categories('Child 3'), $this->categories('Child 2'), $this->categories('Child 1')];
        $this->assertEquals($expected, $children);
    }

    public function testChildrenRelationObeysCustomOrdering()
    {
        with(new OrderedCategorySeeder)->run();

        $children = OrderedCategory::find(1)->children()->get()->all();

        $expected = [OrderedCategory::find(5), OrderedCategory::find(2), OrderedCategory::find(3)];
        $this->assertEquals($expected, $children);
    }

    public function testChildrenRelationAllowsNodeCreation()
    {
        $child = new Category(['name' => 'Child 3.1']);

        $this->categories('Child 3')->children()->save($child);

        $this->assertTrue($child->exists);
        $this->assertEquals($this->categories('Child 3')->getKey(), $child->getParentKey());
    }
}
