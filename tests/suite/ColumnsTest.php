<?php

namespace Baum\Tests;

use Baum\Tests\Support\Models\Category;
use Baum\Tests\Support\Models\Cluster;
use Baum\Tests\Support\Models\MultiScopedCategory;
use Baum\Tests\Support\Models\OrderedCategory;
use Baum\Tests\Support\Models\ScopedCategory;

class ColumnsTest extends TestCase
{
    public function testGetParentColumnName()
    {
        $category = new Category;

        $this->assertEquals($category->getParentColumnName(), 'parent_id');
    }

    public function testGetQualifiedParentColumnName()
    {
        $category = new Category;

        $this->assertEquals($category->getQualifiedParentColumnName(), 'categories.parent_id');
    }

    public function testGetParentKey()
    {
        $this->assertNull($this->categories('Root 1')->getParentKey());

        $this->assertEquals($this->categories('Child 1')->getParentKey(), 1);
    }

    public function testGetLeftColumnName()
    {
        $category = new Category;

        $this->assertEquals($category->getLeftColumnName(), 'left');
    }

    public function testGetQualifiedLeftColumnName()
    {
        $category = new Category;

        $this->assertEquals($category->getQualifiedLeftColumnName(), 'categories.left');
    }

    public function testGetLeft()
    {
        $category = $this->categories('Root 1');

        $this->assertEquals($category->getLeft(), 1);
    }

    public function testGetRightColumnName()
    {
        $category = new Category;

        $this->assertEquals($category->getRightColumnName(), 'right');
    }

    public function testGetQualifiedRightColumnName()
    {
        $category = new Category;

        $this->assertEquals($category->getQualifiedRightColumnName(), 'categories.right');
    }

    public function testGetRight()
    {
        $category = $this->categories('Root 1');

        $this->assertEquals($category->getRight(), 10);
    }

    public function testGetOrderColumName()
    {
        $category = new Category;

        $this->assertEquals($category->getOrderColumnName(), $category->getLeftColumnName());
    }

    public function testGetQualifiedOrderColumnName()
    {
        $category = new Category;

        $this->assertEquals($category->getQualifiedOrderColumnName(), $category->getQualifiedLeftColumnName());
    }

    public function testGetOrder()
    {
        $category = $this->categories('Root 1');

        $this->assertEquals($category->getOrder(), $category->getLeft());
    }

    public function testGetOrderColumnNameNonDefault()
    {
        $category = new OrderedCategory;

        $this->assertEquals($category->getOrderColumnName(), 'name');
    }

    public function testGetQualifiedOrderColumnNameNonDefault()
    {
        $category = new OrderedCategory;

        $this->assertEquals($category->getQualifiedOrderColumnName(), 'categories.name');
    }

    public function testGetOrderNonDefault()
    {
        $category = $this->categories('Root 1', OrderedCategory::class);

        $this->assertEquals($category->getOrder(), 'Root 1');
    }

    public function testGetScopedColumnNames()
    {
        $category = new Category;
        $this->assertEquals($category->getScopedColumnNames(), []);

        $category = new ScopedCategory;
        $this->assertEquals($category->getScopedColumnNames(), ['company_id']);

        $category = new MultiScopedCategory;
        $this->assertEquals($category->getScopedColumnNames(), ['company_id', 'language']);
    }

    public function testGetQualifiedScopedColumnNames()
    {
        $category = new Category;
        $this->assertEquals($category->getQualifiedScopedColumnNames(), []);

        $category = new ScopedCategory;
        $this->assertEquals($category->getQualifiedScopedColumnNames(), ['categories.company_id']);

        $category = new MultiScopedCategory;
        $this->assertEquals($category->getQualifiedScopedColumnNames(), ['categories.company_id', 'categories.language']);
    }

    public function testIsScoped()
    {
        $category = new Category;
        $this->assertFalse($category->isScoped());

        $category = new ScopedCategory;
        $this->assertTrue($category->isScoped());

        $category = new MultiScopedCategory;
        $this->assertTrue($category->isScoped());

        $category = new OrderedCategory();
        $this->assertFalse($category->isScoped());
    }

    public function testNonNumericKey()
    {
        $root = Cluster::root();

        $this->assertTrue(is_string($root->getKey()));
        $this->assertFalse(is_numeric($root->getKey()));
    }

    public function testNonNumericParentKey()
    {
        $child1 = $this->clusters('Child 1');

        $this->assertTrue(is_string($child1->getParentKey()));
        $this->assertFalse(is_numeric($child1->getParentKey()));
    }
}
