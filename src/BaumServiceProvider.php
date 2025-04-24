<?php

namespace Baum;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;

class BaumServiceProvider extends ServiceProvider
{
    /**
     * Baum version string
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMacros();

        $this->registerCommands();
    }

    /**
     * Setup "mixed-in" functionality for some objects.
     *
     * @return void
     */
    public function registerMacros()
    {
        Collection::mixin(new Mixins\Collection);

        Blueprint::mixin(new Mixins\Blueprint);
    }

    /**
     * Register the Horizon Artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\MakeMigrationCommand::class,
                Console\MakeModelCommand::class,
                Console\VersionCommand::class
            ]);
        }
    }
}
