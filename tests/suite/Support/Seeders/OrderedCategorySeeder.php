<?php

namespace Baum\Tests\Support\Seeders;

use Illuminate\Database\Capsule\Manager as DB;

class OrderedCategorySeeder
{
    public function run()
    {
        DB::connection()->transaction(function () {
            DB::table('categories')->delete();

            DB::table('categories')->insert([
                ['id' => 1, 'name' => 'Root Z'   , 'left' => 1  , 'right' => 10 , 'depth' => 0, 'parent_id' => null],
                ['id' => 2, 'name' => 'Child C'  , 'left' => 2  , 'right' => 3  , 'depth' => 1, 'parent_id' => 1],
                ['id' => 3, 'name' => 'Child G'  , 'left' => 4  , 'right' => 7  , 'depth' => 1, 'parent_id' => 1],
                ['id' => 4, 'name' => 'Child G.1', 'left' => 5  , 'right' => 6  , 'depth' => 2, 'parent_id' => 3],
                ['id' => 5, 'name' => 'Child A'  , 'left' => 8  , 'right' => 9  , 'depth' => 1, 'parent_id' => 1],
                ['id' => 6, 'name' => 'Root A'   , 'left' => 11 , 'right' => 12 , 'depth' => 0, 'parent_id' => null],
            ]);

            if (DB::connection()->getDriverName() === 'pgsql') {
                $tablePrefix = DB::connection()->getTablePrefix();

                $sequenceName = $tablePrefix . 'categories_id_seq';

                DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 7');
            }
        });
    }
}
