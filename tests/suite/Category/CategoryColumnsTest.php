<?php

class CategoryColumnsTest extends CategoryTestCase {

  public function testGetParentColumnName() {
    $category = new Category;

    $this->assertEquals(with(new Category)->getParentColumnName(), 'parent_id');
  }

  public function testGetQualifiedParentColumnName() {
    $category = new Category;

    $this->assertEquals($category->getQualifiedParentColumnName(), 'categories.parent_id');
  }

  public function testGetParentId() {
    $this->assertNull($this->categories('Root 1')->getParentId());

    $this->assertEquals($this->categories('Child 1')->getParentId(), 1);
  }

  public function testGetLeftColumnName() {
    $category = new Category;

    $this->assertEquals($category->getLeftColumnName(), 'lft');
  }

  public function testGetQualifiedLeftColumnName() {
    $category = new Category;

    $this->assertEquals($category->getQualifiedLeftColumnName(), 'categories.lft');
  }

  public function testGetLeft() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getLeft(), 1);
  }

  public function testGetRightColumnName() {
    $category = new Category;

    $this->assertEquals($category->getRightColumnName(), 'rgt');
  }

  public function testGetQualifiedRightColumnName() {
    $category = new Category;

    $this->assertEquals($category->getQualifiedRightColumnName(), 'categories.rgt');
  }

  public function testGetRight() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getRight(), 10);
  }

  public function testGetOrderColumName() {
    $category = new Category;

    $this->assertEquals($category->getOrderColumnName(), $category->getLeftColumnName());
  }

  public function testGetQualifiedOrderColumnName() {
    $category = new Category;

    $this->assertEquals($category->getQualifiedOrderColumnName(), $category->getQualifiedLeftColumnName());
  }

  public function testGetOrder() {
    $category = $this->categories('Root 1');

    $this->assertEquals($category->getOrder(), $category->getLeft());
  }

  public function testGetOrderColumnNameNonDefault() {
    $category = new OrderedCategory;

    $this->assertEquals($category->getOrderColumnName(), 'name');
  }

  public function testGetQualifiedOrderColumnNameNonDefault() {
    $category = new OrderedCategory;

    $this->assertEquals($category->getQualifiedOrderColumnName(), 'categories.name');
  }

  public function testGetOrderNonDefault() {
    $category = $this->categories('Root 1', 'OrderedCategory');

    $this->assertEquals($category->getOrder(), 'Root 1');
  }

  public function testGetScopedColumns() {
    $category = new Category;
    $this->assertEquals($category->getScopedColumns(), array());

    $category = new ScopedCategory;
    $this->assertEquals($category->getScopedColumns(), array('company_id'));

    $category = new MultiScopedCategory;
    $this->assertEquals($category->getScopedColumns(), array('company_id', 'language'));
  }

  public function testGetQualifiedScopedColumns() {
    $category = new Category;
    $this->assertEquals($category->getQualifiedScopedColumns(), array());

    $category = new ScopedCategory;
    $this->assertEquals($category->getQualifiedScopedColumns(), array('categories.company_id'));

    $category = new MultiScopedCategory;
    $this->assertEquals($category->getQualifiedScopedColumns(), array('categories.company_id', 'categories.language'));
  }

  public function testIsScoped() {
    $category = new Category;
    $this->assertFalse($category->isScoped());

    $category = new ScopedCategory;
    $this->assertTrue($category->isScoped());

    $category = new MultiScopedCategory;
    $this->assertTrue($category->isScoped());

    $category = new OrderedCategory();
    $this->assertFalse($category->isScoped());
  }

}
