<?php
namespace Baum\Console;

use Baum\Generators\MigrationGenerator;
use Baum\Generators\ModelGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ModelCommand extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'baum:model';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Scaffolds a new model suitable for Baum.';

  /**
   * Model generator instance
   *
   * @var \Baum\Generators\ModelGenerator
   */
  protected $modeler;

  /**
   * Create a new command instance
   *
   * @param ModelGenerator $modeler
   * @return void
   */
  public function __construct(ModelGenerator $modeler) {
    parent::__construct();

    $this->modeler = $modeler;
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

    $output = pathinfo($this->modeler->create($name, $this->getModelsPath()), PATHINFO_FILENAME);

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
   * Get the path to the models directory.
   *
   * @return string
   */
  protected function getModelsPath() {
    return $this->laravel['path.base'];
  }

}
