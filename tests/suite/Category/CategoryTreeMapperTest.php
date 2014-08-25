<?php

use Illuminate\Database\Capsule\Manager as DB;

class CategoryTreeMapperTest extends BaumTestCase {

  public function setUp() {
    with(new CategoryMigrator)->up();
  }

  public function testBuildTree() {
    $tree = array(
      array('id' => 1, 'name' => 'A'),
      array('id' => 2, 'name' => 'B'),
      array('id' => 3, 'name' => 'C', 'children' => array(
        array('id' => 4, 'name' => 'C.1', 'children' => array(
          array('id' => 5, 'name' => 'C.1.1'),
          array('id' => 6, 'name' => 'C.1.2')
        )),
        array('id' => 7, 'name' => 'C.2'),
        array('id' => 8, 'name' => 'C.3')
      )),
      array('id' => 9, 'name' => 'D')
    );
    $this->assertTrue(Category::buildTree($tree));
    $this->assertTrue(Category::isValidNestedSet());

    $hierarchy = Category::all()->toHierarchy()->toArray();
    $this->assertArraysAreEqual($tree, array_ints_keys(hmap($hierarchy, array('id', 'name'))));
  }

  public function testBuildTreePrunesAndInserts() {
    $tree = array(
      array('id' => 1, 'name' => 'A'),
      array('id' => 2, 'name' => 'B'),
      array('id' => 3, 'name' => 'C', 'children' => array(
        array('id' => 4, 'name' => 'C.1', 'children' => array(
          array('id' => 5, 'name' => 'C.1.1'),
          array('id' => 6, 'name' => 'C.1.2')
        )),
        array('id' => 7, 'name' => 'C.2'),
        array('id' => 8, 'name' => 'C.3')
      )),
      array('id' => 9, 'name' => 'D')
    );
    $this->assertTrue(Category::buildTree($tree));
    $this->assertTrue(Category::isValidNestedSet());

    // Postgres fix
    if ( DB::connection()->getDriverName() === 'pgsql' ) {
      $tablePrefix = DB::connection()->getTablePrefix();

      $sequenceName = $tablePrefix . 'categories_id_seq';

      DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 10');
    }

    $updated = array(
      array('id' => 1, 'name' => 'A'),
      array('id' => 2, 'name' => 'B'),
      array('id' => 3, 'name' => 'C', 'children' => array(
        array('id' => 4, 'name' => 'C.1', 'children' => array(
          array('id' => 5, 'name' => 'C.1.1'),
          array('id' => 6, 'name' => 'C.1.2')
        )),
        array('id' => 7, 'name' => 'C.2', 'children' => array(
          array('name' => 'C.2.1'),
          array('name' => 'C.2.2')
        ))
      )),
      array('id' => 9, 'name' => 'D')
    );
    $this->assertTrue(Category::buildTree($updated));
    $this->assertTrue(Category::isValidNestedSet());

    $expected = array(
      array('id' => 1, 'name' => 'A'),
      array('id' => 2, 'name' => 'B'),
      array('id' => 3, 'name' => 'C', 'children' => array(
        array('id' => 4, 'name' => 'C.1', 'children' => array(
          array('id' => 5, 'name' => 'C.1.1'),
          array('id' => 6, 'name' => 'C.1.2')
        )),
        array('id' => 7, 'name' => 'C.2', 'children' => array(
          array('id' => 10, 'name' => 'C.2.1'),
          array('id' => 11, 'name' => 'C.2.2')
        ))
      )),
      array('id' => 9, 'name' => 'D')
    );

    $hierarchy = Category::all()->toHierarchy()->toArray();
    $this->assertArraysAreEqual($expected, array_ints_keys(hmap($hierarchy, array('id', 'name'))));
  }

  public function testMakeTree() {
    with(new CategorySeeder)->run();

    $parent = Category::find(3);

    $subtree = array(
      array('id' => 4, 'name' => 'Child 2.1'),
      array('name' => 'Child 2.2'),
      array('name' => 'Child 2.3', 'children' => array(
        array('name' => 'Child 2.3.1', 'children' => array(
          array('name' => 'Child 2.3.1.1'),
          array('name' => 'Child 2.3.1.1')
        )),
        array('name' => 'Child 2.3.2'),
        array('name' => 'Child 2.3.3')
      )),
      array('name' => 'Child 2.4')
    );

    $this->assertTrue($parent->makeTree($subtree));
    $this->assertTrue(Category::isValidNestedSet());

    $expected = array(
      array('id' => 4, 'name' => 'Child 2.1'),
      array('id' => 7, 'name' => 'Child 2.2'),
      array('id' => 8, 'name' => 'Child 2.3', 'children' => array(
        array('id' => 9, 'name' => 'Child 2.3.1', 'children' => array(
          array('id' => 10, 'name' => 'Child 2.3.1.1'),
          array('id' => 11, 'name' => 'Child 2.3.1.1')
        )),
        array('id' => 12, 'name' => 'Child 2.3.2'),
        array('id' => 13, 'name' => 'Child 2.3.3')
      )),
      array('id' => 14, 'name' => 'Child 2.4')
    );

    $hierarchy = $parent->reload()->getDescendants()->toHierarchy()->toArray();
    $this->assertArraysAreEqual($expected, array_ints_keys(hmap($hierarchy, array('id', 'name'))));
  }

  public function testMakeTreePrunesAndInserts() {
    with(new CategorySeeder)->run();

    $parent = Category::find(3);

    $subtree = array(
      array('id' => 4, 'name' => 'Child 2.1'),
      array('name' => 'Child 2.2'),
      array('name' => 'Child 2.3', 'children' => array(
        array('name' => 'Child 2.3.1', 'children' => array(
          array('name' => 'Child 2.3.1.1'),
          array('name' => 'Child 2.3.1.1')
        )),
        array('name' => 'Child 2.3.2'),
        array('name' => 'Child 2.3.3')
      )),
      array('name' => 'Child 2.4')
    );

    $this->assertTrue($parent->makeTree($subtree));
    $this->assertTrue(Category::isValidNestedSet());

    $expected = array(
      array('id' => 4, 'name' => 'Child 2.1'),
      array('id' => 7, 'name' => 'Child 2.2'),
      array('id' => 8, 'name' => 'Child 2.3', 'children' => array(
        array('id' => 9, 'name' => 'Child 2.3.1', 'children' => array(
          array('id' => 10, 'name' => 'Child 2.3.1.1'),
          array('id' => 11, 'name' => 'Child 2.3.1.1')
        )),
        array('id' => 12, 'name' => 'Child 2.3.2'),
        array('id' => 13, 'name' => 'Child 2.3.3')
      )),
      array('id' => 14, 'name' => 'Child 2.4')
    );

    $hierarchy = $parent->reload()->getDescendants()->toHierarchy()->toArray();
    $this->assertArraysAreEqual($expected, array_ints_keys(hmap($hierarchy, array('id', 'name'))));

    $modified = array(
      array('id' => 7, 'name' => 'Child 2.2'),
      array('id' => 8, 'name' => 'Child 2.3'),
      array('id' => 14, 'name' => 'Child 2.4'),
      array('name' => 'Child 2.5', 'children' => array(
        array('name' => 'Child 2.5.1', 'children' => array(
          array('name' => 'Child 2.5.1.1'),
          array('name' => 'Child 2.5.1.1')
        )),
        array('name' => 'Child 2.5.2'),
        array('name' => 'Child 2.5.3')
      ))
    );

    $this->assertTrue($parent->makeTree($modified));
    $this->assertTrue(Category::isValidNestedSet());

    $expected = array(
      array('id' => 7 , 'name' => 'Child 2.2'),
      array('id' => 8 , 'name' => 'Child 2.3'),
      array('id' => 14, 'name' => 'Child 2.4'),
      array('id' => 15, 'name' => 'Child 2.5', 'children' => array(
        array('id' => 16, 'name' => 'Child 2.5.1', 'children' => array(
          array('id' => 17, 'name' => 'Child 2.5.1.1'),
          array('id' => 18, 'name' => 'Child 2.5.1.1')
        )),
        array('id' => 19, 'name' => 'Child 2.5.2'),
        array('id' => 20, 'name' => 'Child 2.5.3')
      ))
    );

    $hierarchy = $parent->reload()->getDescendants()->toHierarchy()->toArray();
    $this->assertArraysAreEqual($expected, array_ints_keys(hmap($hierarchy, array('id', 'name'))));
  }

}
