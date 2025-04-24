<?php

namespace Baum\Tests\Support\Seeders;

use Illuminate\Database\Capsule\Manager as DB;

class MultiScopedCategorySeeder
{
    public function run()
    {
        DB::connection()->transaction(function () {
            DB::table('categories')->delete();

            DB::table('categories')->insert([
                ['id' => 1 , 'company_id' => 1, 'language' => 'en', 'name' => 'Root 1'     , 'left' => 1  , 'right' => 10 , 'depth' => 0, 'parent_id' => null],
                ['id' => 2 , 'company_id' => 1, 'language' => 'en', 'name' => 'Child 1'    , 'left' => 2  , 'right' => 3  , 'depth' => 1, 'parent_id' => 1],
                ['id' => 3 , 'company_id' => 1, 'language' => 'en', 'name' => 'Child 2'    , 'left' => 4  , 'right' => 7  , 'depth' => 1, 'parent_id' => 1],
                ['id' => 4 , 'company_id' => 1, 'language' => 'en', 'name' => 'Child 2.1'  , 'left' => 5  , 'right' => 6  , 'depth' => 2, 'parent_id' => 3],
                ['id' => 5 , 'company_id' => 1, 'language' => 'en', 'name' => 'Child 3'    , 'left' => 8  , 'right' => 9  , 'depth' => 1, 'parent_id' => 1],
                ['id' => 6 , 'company_id' => 2, 'language' => 'en', 'name' => 'Root 2'     , 'left' => 1  , 'right' => 10 , 'depth' => 0, 'parent_id' => null],
                ['id' => 7 , 'company_id' => 2, 'language' => 'en', 'name' => 'Child 4'    , 'left' => 2  , 'right' => 3  , 'depth' => 1, 'parent_id' => 6],
                ['id' => 8 , 'company_id' => 2, 'language' => 'en', 'name' => 'Child 5'    , 'left' => 4  , 'right' => 7  , 'depth' => 1, 'parent_id' => 6],
                ['id' => 9 , 'company_id' => 2, 'language' => 'en', 'name' => 'Child 5.1'  , 'left' => 5  , 'right' => 6  , 'depth' => 2, 'parent_id' => 8],
                ['id' => 10, 'company_id' => 2, 'language' => 'en', 'name' => 'Child 6'    , 'left' => 8  , 'right' => 9  , 'depth' => 1, 'parent_id' => 6],
                ['id' => 11, 'company_id' => 3, 'language' => 'fr', 'name' => 'Racine 1'   , 'left' => 1  , 'right' => 10 , 'depth' => 0, 'parent_id' => null],
                ['id' => 12, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 1'   , 'left' => 2  , 'right' => 3  , 'depth' => 1, 'parent_id' => 11],
                ['id' => 13, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 2'   , 'left' => 4  , 'right' => 7  , 'depth' => 1, 'parent_id' => 11],
                ['id' => 14, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 2.1' , 'left' => 5  , 'right' => 6  , 'depth' => 2, 'parent_id' => 13],
                ['id' => 15, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 3'   , 'left' => 8  , 'right' => 9  , 'depth' => 1, 'parent_id' => 11],
                ['id' => 16, 'company_id' => 3, 'language' => 'es', 'name' => 'Raiz 1'     ,  'left' => 1  , 'right' => 10, 'depth' => 0, 'parent_id' => null],
                ['id' => 17, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 1'     ,  'left' => 2  , 'right' => 3  , 'depth' => 1, 'parent_id' => 16],
                ['id' => 18, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 2'     ,  'left' => 4  , 'right' => 7  , 'depth' => 1, 'parent_id' => 16],
                ['id' => 19, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 2.1'   ,  'left' => 5  , 'right' => 6  , 'depth' => 2, 'parent_id' => 18],
                ['id' => 20, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 3'     ,  'left' => 8  , 'right' => 9  , 'depth' => 1, 'parent_id' => 16],
            ]);

            if (DB::connection()->getDriverName() === 'pgsql') {
                $tablePrefix = DB::connection()->getTablePrefix();

                $sequenceName = $tablePrefix . 'categories_id_seq';

                DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 21');
            }
        });
    }
}
