<?php
namespace Baum\Providers;

use Baum\Console\MigrationCommand;
use Baum\Console\ModelCommand;
use Baum\Generators\MigrationGenerator;
use Baum\Generators\ModelGenerator;
use Baum\Console\BaumCommand;
use Baum\Console\InstallCommand;
use Illuminate\Support\ServiceProvider;

class BaumServiceProvider extends ServiceProvider {

  /**
   * Baum version
   *
   * @var string
   */
  const VERSION = '1.1.2';

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register() {
    $this->registerCommands();
  }

  /**
   * Register the commands.
   *
   * @return void
   */
  public function registerCommands() {
    $this->registerBaumCommand();
    $this->registerInstallCommand();
    $this->registerMigrationCommand();
    $this->registerModelCommand();

    // Resolve the commands with Artisan by attaching the event listener to Artisan's
    // startup. This allows us to use the commands from our terminal.
    $this->commands('command.baum', 'command.baum.install', 'command.baum.migration', 'command.baum.model');
  }

  /**
   * Register the 'baum' command.
   *
   * @return void
   */
  protected function registerBaumCommand() {
    $this->app->singleton('command.baum', function($app) {
      return new BaumCommand;
    });
  }

  /**
   * Register the 'baum:install' command.
   *
   * @return void
   */
  protected function registerInstallCommand() {
    $this->app->singleton('command.baum.install', function($app) {
      return new InstallCommand();
    });
  }

  /**
   * Register the 'baum:install' command.
   *
   * @return void
   */
  protected function registerMigrationCommand() {
    $this->app->singleton('command.baum.migration', function($app) {
      $migrator = new MigrationGenerator($app['files']);

      return new MigrationCommand($migrator);
    });
  }

  /**
   * Register the 'baum:install' command.
   *
   * @return void
   */
  protected function registerModelCommand() {
    $this->app->singleton('command.baum.model', function($app) {
      $modeler  = new ModelGenerator($app['files']);

      return new ModelCommand($modeler);
    });
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides() {
    return array('command.baum', 'command.baum.install', 'command.baum.migration', 'command.baum.model');
  }

}
