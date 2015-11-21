<?php
namespace Baum\Console;

use Baum\Generators\MigrationGenerator;
use Baum\Generators\ModelGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class MigrationCommand extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'baum:migration';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Scaffolds a new migration and model suitable for Baum.';

  /**
   * Migration generator instance
   *
   * @var \Baum\Generators\MigrationGenerator
   */
  protected $migrator;

  /**
   * Create a new command instance
   *
   * @return void
   */
  public function __construct(MigrationGenerator $migrator) {
    parent::__construct();

    $this->migrator = $migrator;
  }

  /**
   * Execute the console command.
   *
   * Basically, we'll write the migration and model stubs out to disk inflected
   * with the name provided. Once its done, we'll `dump-autoload` for the entire
   * framework to make sure that the new classes are registered by the class
   * loaders.
   *
   * @return void
   */
  public function fire() {
    $name = $this->input->getArgument('name');

    $output = pathinfo($this->migrator->create($name, $this->getMigrationsPath()), PATHINFO_FILENAME);

    $this->line("      <fg=green;options=bold>create</fg=green;options=bold>  $output");
  }

  /**
   * Get the command arguments
   *
   * @return array
   */
  protected function getArguments() {
    return array(
      array('name', InputArgument::REQUIRED, 'Name to use for the scaffolding of the migration and model.')
    );
  }

  /**
   * Get the path to the migrations directory.
   *
   * @return string
   */
  protected function getMigrationsPath() {
    return $this->laravel['path.database'].'/migrations';
  }

}
