<?php
namespace Baum\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

abstract class Generator {

  /**
   * The filesystem instance.
   *
   * @var \Illuminate\Filesystem\Filesystem
   */
  protected $files = NULL;

  /**
   * Create a new MigrationGenerator instance.
   *
   * @param \Illuminate\Filesystem\Filesysmte $files
   * @return void
   */
  function __construct(Filesystem $files) {
    $this->files = $files;
  }

  /**
   * Get the path to the stubs.
   *
   * @return string
   */
  public function getStubPath() {
    return __DIR__.'/stubs';
  }

  /**
   * Get the filesystem instance.
   *
   * @return \Illuminate\Filesystem\Filesystem
   */
  public function getFilesystem() {
    return $this->files;
  }

  /**
   * Get the given stub by name.
   *
   * @param  string  $table
   * @return void
   */
  protected function getStub($name) {
    if ( stripos($name, '.php') === FALSE )
      $name = $name . '.php';

    return $this->files->get($this->getStubPath() . '/' . $name);
  }

  /**
   * Parse the provided stub and replace via the array given.
   *
   * @param string $stub
   * @param string $replacements
   * @return string
   */
  protected function parseStub($stub, $replacements=array()) {
    $output = $stub;

    foreach ($replacements as $key => $replacement) {
      $search = '{{'.$key.'}}';
      $output = str_replace($search, $replacement, $output);
    }

    return $output;
  }

  /**
   * Inflect to a class name
   *
   * @param string $input
   * @return string
   */
  protected function classify($input) {
    return Str::studly(Str::singular($input));
  }

  /**
   * Inflect to table name
   *
   * @param string $input
   * @return string
   */
  protected function tableize($input) {
    return Str::snake(Str::plural($input));
  }
}
