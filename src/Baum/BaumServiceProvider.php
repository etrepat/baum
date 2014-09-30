<?php
namespace Baum;

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
  const VERSION = '1.0.13';

  /**
   * Indicates if loading of the provider is deferred.
   *
   * @var bool
   */
  protected $defer = false;

  /**
   * Bootstrap the application events.
   *
   * @return void
   */
  public function boot() {
    $this->package('baum/baum');
  }

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register() {
    $this->registerCommands();
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides() {
    return array('node');
  }

  /**
   * Register the commands.
   *
   * @return void
   */
  public function registerCommands() {
    $this->registerBaumCommand();
    $this->registerInstallCommand();

    // Resolve the commands with Artisan by attaching the event listener to Artisan's
    // startup. This allows us to use the commands from our terminal.
    $this->commands('command.baum', 'command.baum.install');
  }

  /**
   * Register the 'baum' command.
   *
   * @return void
   */
  protected function registerBaumCommand() {
    $this->app['command.baum'] = $this->app->share(function($app) {
      return new BaumCommand();
    });
  }

  /**
   * Register the 'baum:install' command.
   *
   * @return void
   */
  protected function registerInstallCommand() {
    $this->app['command.baum.install'] = $this->app->share(function($app) {
      $migrator = new MigrationGenerator($app['files']);
      $modeler  = new ModelGenerator($app['files']);

      return new InstallCommand($migrator, $modeler);
    });
  }

}
