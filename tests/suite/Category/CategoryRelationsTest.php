<?php

class CategoryRelationsTest extends CategoryTestCase {

  public function testParentRelationIsABelongsTo() {
    $category = new Category;

    $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $category->parent());
  }

  public function testParentRelationIsSelfReferential() {
    $category = new Category;

    $this->assertInstanceOf('Baum\Node', $category->parent()->getRelated());
  }

  public function testParentRelationRefersToCorrectField() {
    $category = new Category;

    $this->assertEquals($category->getParentColumnName(), $category->parent()->getForeignKey());

    $this->assertEquals($category->getQualifiedParentColumnName(), $category->parent()->getQualifiedForeignKey());
  }

  public function testParentRelation() {
    $this->assertEquals($this->categories('Child 2.1')->parent()->first(), $this->categories('Child 2'));
    $this->assertEquals($this->categories('Child 2')->parent()->first(), $this->categories('Root 1'));
    $this->assertNull($this->categories('Root 1')->parent()->first());
  }

  public function testChildrenRelationIsAHasMany() {
    $category = new Category;

    $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $category->children());
  }

  public function testChildrenRelationIsSelfReferential() {
    $category = new Category;

    $this->assertInstanceOf('Baum\Node', $category->children()->getRelated());
  }

  public function testChildrenRelationReferesToCorrectField() {
    $category = new Category;

    $this->assertEquals($category->getParentColumnName(), $category->children()->getPlainForeignKey());

    $this->assertEquals($category->getQualifiedParentColumnName(), $category->children()->getForeignKey());
  }

  public function testChildrenRelation() {
    $root = $this->categories('Root 1');

    foreach($root->children() as $child)
      $this->assertEquals($root->getKey(), $child->getParentId());

    $expected = array($this->categories('Child 1'), $this->categories('Child 2'), $this->categories('Child 3'));

    $this->assertEquals($expected, $root->children()->get()->all());

    $this->assertEmpty($this->categories('Child 3')->children()->get()->all());
  }

  public function testChildrenRelationUsesDefaultOrdering() {
    $category = new Category;

    $query = $category->children()->getQuery()->getQuery();

    $expected = array('column' => 'lft', 'direction' => 'asc');
    $this->assertEquals($expected, $query->orders[0]);
  }

  public function testChildrenRelationUsesCustomOrdering() {
    $category = new OrderedCategory;

    $query = $category->children()->getQuery()->getQuery();

    $expected = array('column' => 'name', 'direction' => 'asc');
    $this->assertEquals($expected, $query->orders[0]);
  }

  public function testChildrenRelationObeysDefaultOrdering() {
    $children = $this->categories('Root 1')->children()->get()->all();

    $expected = array($this->categories('Child 1'), $this->categories('Child 2'), $this->categories('Child 3'));
    $this->assertEquals($expected, $children);

    // Swap 2 nodes & re-test
    Category::query()->where('id', '=', 2)->update(array('lft' => 8, 'rgt' => 9));
    Category::query()->where('id', '=', 5)->update(array('lft' => 2, 'rgt' => 3));

    $children = $this->categories('Root 1')->children()->get()->all();

    $expected = array($this->categories('Child 3'), $this->categories('Child 2'), $this->categories('Child 1'));
    $this->assertEquals($expected, $children);
  }

  public function testChildrenRelationObeysCustomOrdering() {
    with(new OrderedCategorySeeder)->run();

    $children = OrderedCategory::find(1)->children()->get()->all();

    $expected = array(OrderedCategory::find(5), OrderedCategory::find(2), OrderedCategory::find(3));
    $this->assertEquals($expected, $children);
  }

  public function testChildrenRelationAllowsNodeCreation() {
    $child = new Category(array('name' => 'Child 3.1'));

    $this->categories('Child 3')->children()->save($child);

    $this->assertTrue($child->exists);
    $this->assertEquals($this->categories('Child 3')->getKey(), $child->getParentId());
  }

}
