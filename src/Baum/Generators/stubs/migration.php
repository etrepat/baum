<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class {{class}} extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('{{table}}', function(Blueprint $table) {
      // These columns are needed for Baum's Nested Set implementation to work.
      // Column names may be changed, but they *must* all exist and be modified
      // in the model.
      // Take a look at the model scaffold comments for details.
      $table->increments('id');
      $table->integer('parent_id')->nullable();
      $table->integer('lft')->nullable();
      $table->integer('rgt')->nullable();
      $table->integer('depth')->nullable();

      // Add needed columns here (f.ex: name, slug, path, etc.)
      // $table->string('name', 255);

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::drop('{{table}}');
  }

}
