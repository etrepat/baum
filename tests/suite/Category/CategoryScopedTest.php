<?php

class CategoryScopedTest extends CategoryTestCase {

  public function setUp() {
    with(new MultiScopedCategorySeeder)->run();
  }

  public function testInSameScope() {
    $root1  = $this->categories('Root 1', 'ScopedCategory');
    $child1 = $this->categories('Child 1', 'ScopedCategory');
    $child2 = $this->categories('Child 2', 'ScopedCategory');

    $root2  = $this->categories('Root 2', 'ScopedCategory');
    $child4 = $this->categories('Child 4', 'ScopedCategory');
    $child5 = $this->categories('Child 5', 'ScopedCategory');

    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $this->assertTrue($root1->inSameScope($child1));
    $this->assertTrue($child1->inSameScope($child2));
    $this->assertTrue($child2->inSameScope($root1));

    $this->assertTrue($root2->inSameScope($child4));
    $this->assertTrue($child4->inSameScope($child5));
    $this->assertTrue($child5->inSameScope($root2));

    $this->assertFalse($root1->inSameScope($root2));
    $this->assertFalse($root2->inSameScope($root1));

    $this->assertFalse($child1->inSameScope($child4));
    $this->assertFalse($child4->inSameScope($child1));

    $this->assertFalse($child2->inSameScope($child5));
    $this->assertFalse($child5->inSameScope($child2));
  }

  public function testInSameScopeMultiple() {
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $child1 = $this->categories('Child 1', 'MultiScopedCategory');
    $child2 = $this->categories('Child 2', 'MultiScopedCategory');

    $child4 = $this->categories('Child 4', 'MultiScopedCategory');
    $child5 = $this->categories('Child 5', 'MultiScopedCategory');

    $enfant1 = $this->categories('Enfant 1', 'MultiScopedCategory');
    $enfant2 = $this->categories('Enfant 2', 'MultiScopedCategory');

    $hijo1 = $this->categories('Hijo 1', 'MultiScopedCategory');
    $hijo2 = $this->categorieS('Hijo 2', 'MultiScopedCategory');

    $this->assertTrue($child1->inSameScope($child2));
    $this->assertTrue($child4->inSameScope($child5));
    $this->assertTrue($enfant1->inSameScope($enfant2));
    $this->assertTrue($hijo1->inSameScope($hijo2));

    $this->assertFalse($child2->inSameScope($child4));
    $this->assertFalse($child5->inSameScope($enfant1));
    $this->assertFalse($enfant2->inSameScope($hijo1));
    $this->assertFalse($hijo2->inSameScope($child1));
  }

  public function testIsSelfOrAncestorOf() {
    $root1 = $this->categories('Root 1', 'ScopedCategory');
    $child21 = $this->categories('Child 2.1', 'ScopedCategory');

    $root2 = $this->categories('Root 2', 'ScopedCategory');
    $child51 = $this->categories('Child 5.1', 'ScopedCategory');

    $this->assertTrue($root1->isSelfOrAncestorOf($child21));
    $this->assertTrue($root2->isSelfOrAncestorOf($child51));

    $this->assertFalse($root1->isSelfOrAncestorOf($child51));
    $this->assertFalse($root2->isSelfOrAncestorOf($child21));
  }

  public function testIsSelfOrDescendantOf() {
    $root1 = $this->categories('Root 1', 'ScopedCategory');
    $child21 = $this->categories('Child 2.1', 'ScopedCategory');

    $root2 = $this->categories('Root 2', 'ScopedCategory');
    $child51 = $this->categories('Child 5.1', 'ScopedCategory');

    $this->assertTrue($child21->isSelfOrDescendantOf($root1));
    $this->assertTrue($child51->isSelfOrDescendantOf($root2));

    $this->assertFalse($child21->isSelfOrDescendantOf($root2));
    $this->assertFalse($child51->isSelfOrDescendantOf($root1));
  }

  public function testGetSiblingsAndSelf() {
    $root2  = $this->categories('Root 2', 'ScopedCategory');

    $child1 = $this->categories('Child 1', 'ScopedCategory');
    $child2 = $this->categories('Child 2', 'ScopedCategory');
    $child3 = $this->categories('Child 3', 'ScopedCategory');

    $expected = array($root2);
    $this->assertEquals($expected, $root2->getSiblingsAndSelf()->all());

    $expected = array($child1, $child2, $child3);
    $this->assertEquals($expected, $child2->getSiblingsAndSelf()->all());
  }

  public function testGetSiblingsAndSelfMultiple() {
    $root1  = $this->categories('Racine 1', 'MultiScopedCategory');

    $child1 = $this->categories('Hijo 1', 'MultiScopedCategory');
    $child2 = $this->categories('Hijo 2', 'MultiScopedCategory');
    $child3 = $this->categories('Hijo 3', 'MultiScopedCategory');

    $expected = array($root1);
    $this->assertEquals($expected, $root1->getSiblingsAndSelf()->all());

    $expected = array($child1, $child2, $child3);
    $this->assertEquals($expected, $child3->getSiblingsAndSelf()->all());
  }

  public function testSimpleMovements() {
    with(new ScopedCategorySeeder)->run();

    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $root3 = ScopedCategory::create(array('name' => 'Root 3', 'company_id' => 2));
    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $this->categories('Child 6', 'ScopedCategory')->makeChildOf($root3);
    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $root3->reload();
    $expected = array($this->categories('Child 6', 'ScopedCategory'));
    $this->assertEquals($expected, $root3->children()->get()->all());
  }

  public function testSimpleSubtreeMovements() {
    with(new ScopedCategorySeeder)->run();

    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $root3 = ScopedCategory::create(array('name' => 'Root 3', 'company_id' => 2));
    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $this->categories('Child 5', 'ScopedCategory')->makeChildOf($root3);
    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $root3->reload();
    $expected = array(
      $this->categories('Child 5', 'ScopedCategory'),
      $this->categories('Child 5.1', 'ScopedCategory')
    );
    $this->assertEquals($expected, $root3->getDescendants()->all());
  }

  public function testFullSubtreeMovements() {
    with(new ScopedCategorySeeder)->run();

    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $root3 = ScopedCategory::create(array('name' => 'Root 3', 'company_id' => 2));
    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $this->categories('Root 2', 'ScopedCategory')->makeChildOf($root3);
    $this->assertTrue(ScopedCategory::isValidNestedSet());

    $root3->reload();
    $expected = array(
      $this->categories('Root 2'    , 'ScopedCategory'),
      $this->categories('Child 4'   , 'ScopedCategory'),
      $this->categories('Child 5'   , 'ScopedCategory'),
      $this->categories('Child 5.1' , 'ScopedCategory'),
      $this->categories('Child 6'   , 'ScopedCategory')
    );
    $this->assertEquals($expected, $root3->getDescendants()->all());
  }

  public function testSimpleMovementsMultiple() {
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $root2 = MultiScopedCategory::create(array('name' => 'Raiz 2', 'company_id' => 3, 'language' => 'es'));
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $this->categories('Hijo 1', 'MultiScopedCategory')->makeChildOf($root2);
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $root2->reload();
    $expected = array($this->categories('Hijo 1', 'MultiScopedCategory'));
    $this->assertEquals($expected, $root2->children()->get()->all());
  }

  public function testSimpleSubtreeMovementsMultiple() {
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $root2 = MultiScopedCategory::create(array('name' => 'Raiz 2', 'company_id' => 3, 'language' => 'es'));
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $this->categories('Hijo 2', 'MultiScopedCategory')->makeChildOf($root2);
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $root2->reload();
    $expected = array(
      $this->categories('Hijo 2', 'MultiScopedCategory'),
      $this->categories('Hijo 2.1', 'MultiScopedCategory')
    );
    $this->assertEquals($expected, $root2->getDescendants()->all());
  }

  public function testFullSubtreeMovementsMultiple() {
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $root2 = MultiScopedCategory::create(array('name' => 'Raiz 2', 'company_id' => 3, 'language' => 'es'));
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $this->categories('Raiz 1', 'MultiScopedCategory')->makeChildOf($root2);
    $this->assertTrue(MultiScopedCategory::isValidNestedSet());

    $root2->reload();
    $expected = array(
      $this->categories('Raiz 1', 'MultiScopedCategory'),
      $this->categories('Hijo 1', 'MultiScopedCategory'),
      $this->categories('Hijo 2', 'MultiScopedCategory'),
      $this->categories('Hijo 2.1', 'MultiScopedCategory'),
      $this->categories('Hijo 3', 'MultiScopedCategory')
    );
    $this->assertEquals($expected, $root2->getDescendants()->all());
  }

  public function testToHierarchyNestsCorrectlyWithScopedOrder() {
    with(new OrderedScopedCategorySeeder)->run();

    $expectedWhole1 = array(
      'Root 1' => array(
          'Child 1' => null,
          'Child 2' => array (
              'Child 2.1' => null
          ),
          'Child 3' => null
      )
    );

    $expectedWhole2 = array(
      'Root 2' => array(
          'Child 4' => null,
          'Child 5' => array (
              'Child 5.1' => null
          ),
          'Child 6' => null
      )
    );

    $this->assertArraysAreEqual($expectedWhole1, hmap(OrderedScopedCategory::where('company_id', 1)->get()->toHierarchy()->toArray()));
    $this->assertArraysAreEqual($expectedWhole2, hmap(OrderedScopedCategory::where('company_id', 2)->get()->toHierarchy()->toArray()));
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotMoveBetweenScopes() {
    $child4 = $this->categories('Child 4', 'ScopedCategory');
    $root1 = $this->categories('Root 1', 'ScopedCategory');

    $child4->makeChildOf($root1);
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotMoveBetweenScopesMultiple() {
    $root1 = $this->categories('Root 1', 'MultiScopedCategory');
    $child4 = $this->categories('Child 4', 'MultiScopedCategory');

    $child4->makeChildOf($root1);
  }

  /**
   * @expectedException Baum\MoveNotPossibleException
   */
  public function testNodesCannotMoveBetweenScopesMultiple2() {
    $root1 = $this->categories('Racine 1', 'MultiScopedCategory');
    $child2 = $this->categories('Hijo 2', 'MultiScopedCategory');

    $child2->makeChildOf($root1);
  }

  // TODO: Moving nodes between scopes is problematic ATM. Fix it or find a work-around.
  public function testMoveNodeBetweenScopes() {
    $this->markTestSkipped();

    // $root1    = Menu::create(array('caption' => 'TL1', 'site_id' => 1, 'language' => 'en'));
    // $child11  = Menu::create(array('caption' => 'C11', 'site_id' => 1, 'language' => 'en'));
    // $child12  = Menu::create(array('caption' => 'C12', 'site_id' => 1, 'language' => 'en'));

    // $this->assertTrue(Menu::isValidNestedSet());

    // $child11->makeChildOf($root1);
    // $child12->makeChildOf($root1);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $root2    = Menu::create(array('caption' => 'TL2', 'site_id' => 2, 'language' => 'en'));
    // $child21  = Menu::create(array('caption' => 'C21', 'site_id' => 2, 'language' => 'en'));
    // $child22  = Menu::create(array('caption' => 'C22', 'site_id' => 2, 'language' => 'en'));
    // $child21->makeChildOf($root2);
    // $child22->makeChildOf($root2);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $child11->update(array('site_id' => 2));
    // $child11->makeChildOf($root2);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $expected = array($this->menus('C12'));
    // $this->assertEquals($expected, $root1->children()->get()->all());

    // $expected = array($this->menus('C21'), $this->menus('C22'), $this->menus('C11'));
    // $this->assertEquals($expected, $root2->children()->get()->all());
  }

}
