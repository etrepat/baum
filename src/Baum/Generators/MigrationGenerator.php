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

    $this->files->put($path, $this->parseStub($stub, array(
      'table' => $this->tableize($name),
      'class' => $this->getMigrationClassName($name)
    )));

    return $path;
  }

  /**
   * Get the migration name.
   *
   * @param string $name
   * @return string
   */
  protected function getMigrationName($name) {
    return 'create_' . $this->tableize($name) . '_table';
  }

  /**
   * Get the name for the migration class.
   *
   * @param string $name
   */
  protected function getMigrationClassName($name) {
    return $this->classify($this->getMigrationName($name));
  }

  /**
   * Get the full path name to the migration.
   *
   * @param  string  $name
   * @param  string  $path
   * @return string
   */
  protected function getPath($name, $path) {
    return $path . '/' . $this->getDatePrefix() . '_' . $this->getMigrationName($name) . '.php';
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
