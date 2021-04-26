<?php

class CategoryTestCase extends BaumTestCase {

  public static function setUpBeforeClass() {
    with(new CategoryMigrator)->up();
  }

  public function setUp() {
    with(new CategorySeeder)->run();
  }

  protected function categories($name, $className = 'Category') {
    return forward_static_call_array(array($className, 'where'), array('name', '=', $name))->first();
  }

}
