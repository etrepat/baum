<?php

class CategoryTreeRebuildingTest extends CategoryTestCase {

  public function testRebuild() {
    $this->assertTrue(Category::isValidNestedSet());

    $root = Category::root();
    Category::query()->update(array('lft' => null, 'rgt' => null));
    $this->assertFalse(Category::isValidNestedSet());

    Category::rebuild();
    $this->assertTrue(Category::isValidNestedSet());

    $this->assertEquals($root, Category::root());
  }

  public function testRebuildPresevesRootNodes() {
    $root1 = Category::create(array('name' => 'Test Root 1'));
    $root2 = Category::create(array('name' => 'Test Root 2'));
    $root3 = Category::create(array('name' => 'Test Root 3'));

    $root2->makeChildOf($root1);
    $root3->makeChildOf($root1);

    $lastRoot = Category::roots()->reOrderBy($root1->getLeftColumnName(), 'desc')->first();

    Category::query()->update(array('lft' => null, 'rgt' => null));
    Category::rebuild();

    $this->assertEquals($lastRoot, Category::roots()->reOrderBy($root1->getLeftColumnName(), 'desc')->first());
  }

  public function testRebuildRecomputesDepth() {
    $this->assertTrue(Category::isValidNestedSet());

    Category::query()->update(array('lft' => null, 'rgt' => null, 'depth' => 0));
    $this->assertFalse(Category::isValidNestedSet());

    Category::rebuild();

    $expected = array(0, 1, 1, 2, 1, 0);
    $this->assertEquals($expected, Category::all()->map(function($n) { return $n->getDepth(); })->all());
  }

  public function testRebuildWithScope() {
    MultiScopedCategory::query()->delete();

    $root   = MultiScopedCategory::create(array('name' => 'A'   , 'company_id' => 721, 'language' => 'es'));
    $child1 = MultiScopedCategory::create(array('name' => 'A.1' , 'company_id' => 721, 'language' => 'es'));
    $child2 = MultiscopedCategory::create(array('name' => 'A.2' , 'company_id' => 721, 'language' => 'es'));

    $child1->makeChildOf($root);
    $child2->makeChildOf($root);

    MultiscopedCategory::query()->update(array('lft' => null, 'rgt' => null));
    $this->assertFalse(MultiscopedCategory::isValidNestedSet());

    MultiscopedCategory::rebuild();
    $this->assertTrue(MultiscopedCategory::isValidNestedSet());

    $this->assertEquals($root, $this->categories('A', 'MultiScopedCategory'));

    $expected = array($child1, $child2);
    $this->assertEquals($expected, $this->categories('A', 'MultiScopedCategory')->children()->get()->all());
  }

  public function testRebuildWithMultipleScopes() {
    MultiScopedCategory::query()->delete();

    $root1    = MultiScopedCategory::create(array('name' => 'TL1', 'company_id' => 1, 'language' => 'en'));
    $child11  = MultiScopedCategory::create(array('name' => 'C11', 'company_id' => 1, 'language' => 'en'));
    $child12  = MultiScopedCategory::create(array('name' => 'C12', 'company_id' => 1, 'language' => 'en'));
    $child11->makeChildOf($root1);
    $child12->makeChildOf($root1);

    $root2    = MultiScopedCategory::create(array('name' => 'TL2', 'company_id' => 2, 'language' => 'en'));
    $child21  = MultiScopedCategory::create(array('name' => 'C21', 'company_id' => 2, 'language' => 'en'));
    $child22  = MultiScopedCategory::create(array('name' => 'C22', 'company_id' => 2, 'language' => 'en'));
    $child21->makeChildOf($root2);
    $child22->makeChildOf($root2);

    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $tree = MultiScopedCategory::query()->orderBy($root1->getKeyName())->get()->all();

    MultiScopedCategory::query()->update(array('lft' => null, 'rgt' => null));
    MultiScopedCategory::rebuild();

    $this->assertTrue(MultiScopedCategory::isValidNestedSet());
    $this->assertEquals($tree, MultiScopedCategory::query()->orderBy($root1->getKeyName())->get()->all());
  }

}
