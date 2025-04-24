<?php

namespace Baum\Tests\Support\Migrators;

use Illuminate\Database\Capsule\Manager as DB;

class CategoryMigrator
{
    public function up()
    {
        DB::connection()->transaction(function () {
            DB::schema()->dropIfExists('categories');

            DB::schema()->create('categories', function ($t) {
                $t->increments('id');

                $t->nestedSet();

                // $t->integer('parent_id')->unsigned()->nullable()->index();
                // $t->foreign('parent_id')->references('id')->on('categories');
                // $t->integer('left')->unsigned()->nullable()->index();
                // $t->integer('right')->unsigned()->nullable()->index();
                // $t->integer('depth')->unsigned()->nullable()->index();

                $t->string('name')->index();

                $t->integer('company_id')->unsigned()->nullable()->index();
                $t->string('language', 3)->nullable()->index();

                $t->timestamp('created_at')->nullable();
                $t->timestamp('updated_at')->nullable();

                $t->softDeletes();
            });
        });
    }
}
