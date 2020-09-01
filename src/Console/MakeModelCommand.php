<?php

namespace Baum\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeModelCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'baum:make-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent model class suitable for Baum';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/model.stub';
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = $this->getMigrationTableName();

        $this->call('baum:make-migration', ['name' => "create_{$table}_table", '--create' => $table]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],

            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
        ];
    }

    /**
     * Get the migration table name.
     *
     * @return string
     */
    public function getMigrationTableName()
    {
        if (method_exists(Str::class, 'pluralStudly')) {
            return Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));
        }

        return Str::plural(Str::snake(class_basename($this->argument('name'))));
    }
}
