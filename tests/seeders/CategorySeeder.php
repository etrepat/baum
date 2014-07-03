<?php

use Illuminate\Database\Capsule\Manager as DB;

class CategorySeeder {

  public function run() {
    DB::table('categories')->delete();

    Category::unguard();

    Category::create(array('id' => 1, 'name' => 'Root 1'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    Category::create(array('id' => 2, 'name' => 'Child 1'  , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 1));
    Category::create(array('id' => 3, 'name' => 'Child 2'  , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 1));
    Category::create(array('id' => 4, 'name' => 'Child 2.1', 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 3));
    Category::create(array('id' => 5, 'name' => 'Child 3'  , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 1));
    Category::create(array('id' => 6, 'name' => 'Root 2'   , 'lft' => 11 , 'rgt' => 12 , 'depth' => 0));

    Category::reguard();

    if ( DB::connection()->getDriverName() === 'pgsql' ) {
      $tablePrefix = DB::connection()->getTablePrefix();

      $sequenceName = $tablePrefix . 'categories_id_seq';

      DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 7');
    }
  }

  public function nestUptoAt($node, $levels=10, $attrs=array()) {
    for($i=0; $i < $levels; $i++, $node=$new) {
      $new = Category::create(array_merge($attrs, array('name' => "{$node->name}.1")));
      $new->makeChildOf($node);
    }
  }

}

class ScopedCategorySeeder {

  public function run() {
    DB::table('categories')->delete();

    ScopedCategory::unguard();

    ScopedCategory::create(array('id' => 1 , 'company_id' => 1, 'name' => 'Root 1'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    ScopedCategory::create(array('id' => 2 , 'company_id' => 1, 'name' => 'Child 1'  , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 1));
    ScopedCategory::create(array('id' => 3 , 'company_id' => 1, 'name' => 'Child 2'  , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 1));
    ScopedCategory::create(array('id' => 4 , 'company_id' => 1, 'name' => 'Child 2.1', 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 3));
    ScopedCategory::create(array('id' => 5 , 'company_id' => 1, 'name' => 'Child 3'  , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 1));
    ScopedCategory::create(array('id' => 6 , 'company_id' => 2, 'name' => 'Root 2'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    ScopedCategory::create(array('id' => 7 , 'company_id' => 2, 'name' => 'Child 4'  , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 6));
    ScopedCategory::create(array('id' => 8 , 'company_id' => 2, 'name' => 'Child 5'  , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 6));
    ScopedCategory::create(array('id' => 9 , 'company_id' => 2, 'name' => 'Child 5.1', 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 8));
    ScopedCategory::create(array('id' => 10, 'company_id' => 2, 'name' => 'Child 6'  , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 6));

    ScopedCategory::reguard();

    if ( DB::connection()->getDriverName() === 'pgsql' ) {
      $tablePrefix = DB::connection()->getTablePrefix();

      $sequenceName = $tablePrefix . 'categories_id_seq';

      DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 11');
    }
  }

}

class MultiScopedCategorySeeder {

  public function run() {
    DB::table('categories')->delete();

    MultiScopedCategory::unguard();

    MultiScopedCategory::create(array('id' => 1 , 'company_id' => 1, 'language' => 'en', 'name' => 'Root 1'     , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    MultiScopedCategory::create(array('id' => 2 , 'company_id' => 1, 'language' => 'en', 'name' => 'Child 1'    , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 1));
    MultiScopedCategory::create(array('id' => 3 , 'company_id' => 1, 'language' => 'en', 'name' => 'Child 2'    , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 1));
    MultiScopedCategory::create(array('id' => 4 , 'company_id' => 1, 'language' => 'en', 'name' => 'Child 2.1'  , 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 3));
    MultiScopedCategory::create(array('id' => 5 , 'company_id' => 1, 'language' => 'en', 'name' => 'Child 3'    , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 1));
    MultiScopedCategory::create(array('id' => 6 , 'company_id' => 2, 'language' => 'en', 'name' => 'Root 2'     , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    MultiScopedCategory::create(array('id' => 7 , 'company_id' => 2, 'language' => 'en', 'name' => 'Child 4'    , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 6));
    MultiScopedCategory::create(array('id' => 8 , 'company_id' => 2, 'language' => 'en', 'name' => 'Child 5'    , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 6));
    MultiScopedCategory::create(array('id' => 9 , 'company_id' => 2, 'language' => 'en', 'name' => 'Child 5.1'  , 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 8));
    MultiScopedCategory::create(array('id' => 10, 'company_id' => 2, 'language' => 'en', 'name' => 'Child 6'    , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 6));
    MultiScopedCategory::create(array('id' => 11, 'company_id' => 3, 'language' => 'fr', 'name' => 'Racine 1'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    MultiScopedCategory::create(array('id' => 12, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 1'   , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 11));
    MultiScopedCategory::create(array('id' => 13, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 2'   , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 11));
    MultiScopedCategory::create(array('id' => 14, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 2.1' , 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 13));
    MultiScopedCategory::create(array('id' => 15, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 3'   , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 11));
    MultiScopedCategory::create(array('id' => 16, 'company_id' => 3, 'language' => 'es', 'name' => 'Raiz 1'     ,  'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    MultiScopedCategory::create(array('id' => 17, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 1'     ,  'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 16));
    MultiScopedCategory::create(array('id' => 18, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 2'     ,  'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 16));
    MultiScopedCategory::create(array('id' => 19, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 2.1'   ,  'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 18));
    MultiScopedCategory::create(array('id' => 20, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 3'     ,  'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 16));

    MultiScopedCategory::reguard();

    if ( DB::connection()->getDriverName() === 'pgsql' ) {
      $tablePrefix = DB::connection()->getTablePrefix();

      $sequenceName = $tablePrefix . 'categories_id_seq';

      DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 21');
    }
  }

}

class OrderedCategorySeeder {

  public function run() {
    DB::table('categories')->delete();

    OrderedCategory::unguard();

    OrderedCategory::create(array('id' => 1, 'name' => 'Root Z'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    OrderedCategory::create(array('id' => 2, 'name' => 'Child C'  , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 1));
    OrderedCategory::create(array('id' => 3, 'name' => 'Child G'  , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 1));
    OrderedCategory::create(array('id' => 4, 'name' => 'Child G.1', 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 3));
    OrderedCategory::create(array('id' => 5, 'name' => 'Child A'  , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 1));
    OrderedCategory::create(array('id' => 6, 'name' => 'Root A'   , 'lft' => 11 , 'rgt' => 12 , 'depth' => 0));

    OrderedCategory::reguard();

    if ( DB::connection()->getDriverName() === 'pgsql' ) {
      $tablePrefix = DB::connection()->getTablePrefix();

      $sequenceName = $tablePrefix . 'categories_id_seq';

      DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 7');
    }
  }

}

class OrderedScopedCategorySeeder {

  public function run() {
    DB::table('categories')->delete();

    OrderedScopedCategory::unguard();

    OrderedScopedCategory::create(array('id' => 1 , 'company_id' => 1, 'name' => 'Root 1'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    OrderedScopedCategory::create(array('id' => 2 , 'company_id' => 1, 'name' => 'Child 3'  , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 1));
    OrderedScopedCategory::create(array('id' => 3 , 'company_id' => 1, 'name' => 'Child 2'  , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 1));
    OrderedScopedCategory::create(array('id' => 4 , 'company_id' => 1, 'name' => 'Child 2.1', 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 3));
    OrderedScopedCategory::create(array('id' => 5 , 'company_id' => 1, 'name' => 'Child 1'  , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 1));
    OrderedScopedCategory::create(array('id' => 6 , 'company_id' => 2, 'name' => 'Root 2'   , 'lft' => 1  , 'rgt' => 10 , 'depth' => 0));
    OrderedScopedCategory::create(array('id' => 7 , 'company_id' => 2, 'name' => 'Child 4'  , 'lft' => 2  , 'rgt' => 3  , 'depth' => 1, 'parent_id' => 6));
    OrderedScopedCategory::create(array('id' => 8 , 'company_id' => 2, 'name' => 'Child 5'  , 'lft' => 4  , 'rgt' => 7  , 'depth' => 1, 'parent_id' => 6));
    OrderedScopedCategory::create(array('id' => 9 , 'company_id' => 2, 'name' => 'Child 5.1', 'lft' => 5  , 'rgt' => 6  , 'depth' => 2, 'parent_id' => 8));
    OrderedScopedCategory::create(array('id' => 10, 'company_id' => 2, 'name' => 'Child 6'  , 'lft' => 8  , 'rgt' => 9  , 'depth' => 1, 'parent_id' => 6));

    OrderedScopedCategory::reguard();

    if ( DB::connection()->getDriverName() === 'pgsql' ) {
      $tablePrefix = DB::connection()->getTablePrefix();

      $sequenceName = $tablePrefix . 'categories_id_seq';

      DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 11');
    }
  }

}
