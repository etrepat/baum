<?php

namespace Baum\Tests\Support\Migrators;

use Illuminate\Database\Capsule\Manager as DB;

class ClusterMigrator
{
    public function up()
    {
        DB::connection()->transaction(function () {
            DB::schema()->dropIfExists('clusters');

            DB::schema()->create('clusters', function ($t) {
                $t->string('id')->primary();

                $t->string('parent_id')->nullable()->index();
                $t->integer('left')->unsigned()->nullable()->index();
                $t->integer('right')->unsigned()->nullable()->index();
                $t->integer('depth')->unsigned()->nullable()->index();

                $t->string('name')->index();

                $t->integer('company_id')->unsigned()->nullable()->index();
                $t->string('language', 3)->nullable()->index();

                $t->timestamp('created_at')->nullable();
                $t->timestamp('updated_at')->nullable();

                $t->softDeletes();
            });

            DB::schema()->table('clusters', function ($t) {
                $t->foreign('parent_id')->references('id')->on('clusters');
            });
        });
    }
}
