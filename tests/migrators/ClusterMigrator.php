<?php

use Illuminate\Database\Capsule\Manager as DB;

class ClusterMigrator {

  public function up() {
    DB::schema()->dropIfExists('clusters');

    DB::schema()->create('clusters', function($t) {
      $t->string('id');

      $t->string('parent_id')->nullable();
      $t->integer('lft')->nullable();
      $t->integer('rgt')->nullable();
      $t->integer('depth')->nullable();

      $t->string('name');

      $t->integer('company_id')->unsigned()->nullable();
      $t->string('language', 3)->nullable();

      $t->timestamp('created_at')->nullable();
      $t->timestamp('updated_at')->nullable();

      $t->softDeletes();
    });
  }

  public function down() {
    DB::schema()->drop('clusters');
  }

}
