<?php
namespace Baum\Generators;

class MigrationGenerator extends Generator {

  /**
   * Create a new migration at the given path.
   *
   * @param  string  $name
   * @param  string  $path
   * @return string
   */
  public function create($name, $path) {
    $path = $this->getPath($name, $path);

    $stub = $this->getStub('migration');

    $this->files->put($path, $this->parseStub($stub, [
      'table' => $this->tableize($name),
      'class' => $this->classify($name)
    ]));

    return $path;
  }

  /**
   * Get the full path name to the migration.
   *
   * @param  string  $name
   * @param  string  $path
   * @return string
   */
  protected function getPath($name, $path) {
    return $path . '/' . $this->getDatePrefix() . '_create_' . $this->tableize($name) . '_table.php';
  }

  /**
   * Get the date prefix for the migration.
   *
   * @return int
   */
  protected function getDatePrefix() {
    return date('Y_m_d_His');
  }

}
