<?php

namespace Baum\Tests\Support\Seeders;

use Illuminate\Database\Capsule\Manager as DB;
use Baum\Tests\Support\Models\Category;

class CategorySeeder
{
    public function run()
    {
        DB::connection()->transaction(function () {
            DB::table('categories')->delete();

            DB::table('categories')->insert([
                ['id' => 1, 'name' => 'Root 1', 'left' => 1, 'right' => 10 , 'depth' => 0, 'parent_id' => null],
                ['id' => 2, 'name' => 'Child 1', 'left' => 2, 'right' => 3, 'depth' => 1, 'parent_id' => 1],
                ['id' => 3, 'name' => 'Child 2', 'left' => 4, 'right' => 7, 'depth' => 1, 'parent_id' => 1],
                ['id' => 4, 'name' => 'Child 2.1', 'left' => 5, 'right' => 6, 'depth' => 2, 'parent_id' => 3],
                ['id' => 5, 'name' => 'Child 3', 'left' => 8, 'right' => 9, 'depth' => 1, 'parent_id' => 1],
                ['id' => 6, 'name' => 'Root 2', 'left' => 11, 'right' => 12, 'depth' => 0, 'parent_id' => null],
            ]);

            if (DB::connection()->getDriverName() === 'pgsql') {
                $tablePrefix = DB::connection()->getTablePrefix();

                $sequenceName = $tablePrefix . 'categories_id_seq';

                DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 7');
            }
        });
    }

    public function nestUptoAt($node, $levels = 10, $attrs = [])
    {
        for ($i=$levels; $i > 0; $i--) {
            $nested = Category::create(array_merge($attrs, ['name' => "{$node->name}.1"]));

            $nested->makeChildOf($node);

            $node = $nested->fresh();
        }
    }
}
