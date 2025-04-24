<?php

namespace Baum\Tests\Support\Seeders;

use Illuminate\Database\Capsule\Manager as DB;
use Baum\Tests\Support\Models\OrderedCluster;

class OrderedClusterSeeder
{
    public function run()
    {
        DB::connection()->transaction(function () {
            DB::table('clusters')->delete();

            DB::table('clusters')->insert([
                ['id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1', 'name' => 'Root Z'   , 'left' => 1  , 'right' => 10 , 'depth' => 0, 'parent_id' => null],
                ['id' => '5d7ce1fd-6151-46d3-a5b3-0ebb9988dc57', 'name' => 'Child C'  , 'left' => 2  , 'right' => 3  , 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1'],
                ['id' => '07c1fc8c-53b5-4fe7-b9c4-e09f266a455c', 'name' => 'Child G'  , 'left' => 4  , 'right' => 7  , 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1'],
                ['id' => '3315a297-af87-4ad3-9fa5-19785407573d', 'name' => 'Child G.1', 'left' => 5  , 'right' => 6  , 'depth' => 2, 'parent_id' => '07c1fc8c-53b5-4fe7-b9c4-e09f266a455c'],
                ['id' => '054476d2-6830-4014-a181-4de010ef7114', 'name' => 'Child A'  , 'left' => 8  , 'right' => 9  , 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1'],
                ['id' => '3bb62314-9e1e-49c6-a5cb-17a9ab9b1b9a', 'name' => 'Root A'   , 'left' => 11 , 'right' => 12 , 'depth' => 0, 'parent_id' => null],
            ]);
        });
    }
}
