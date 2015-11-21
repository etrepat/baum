<?php
namespace Baum\Console;

use Baum\Generators\MigrationGenerator;
use Baum\Generators\ModelGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'baum:install';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Scaffolds a new migration and model suitable for Baum.';

  /**
   * Create a new command instance
   *
   * @return void
   */
  public function __construct() {
    parent::__construct();
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

    $this->call('baum:migration', ['name' => $name]);
    $this->call('baum:model', ['name' => $name]);
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

}
