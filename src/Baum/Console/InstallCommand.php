<?php
namespace Baum\Console;

use Illuminate\Console\Command;

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
   * Execute the console command.
   *
   * @return void
   */
  public function fire() {
    $this->line('Hello from baum:install');
  }

}
