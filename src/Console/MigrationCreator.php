<?php

namespace Baum\Console;

use Illuminate\Database\Migrations\MigrationCreator as BaseMigrationCreator;
use Illuminate\Filesystem\Filesystem;

class MigrationCreator extends BaseMigrationCreator
{
    /**
     * Create a new migration creator instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $customStubPath
     * @return void
     */
    public function __construct(Filesystem $files, $customStubPath = null)
    {
        parent::__construct($files, $customStubPath);
    }

    /**
     * Get the migration stub file.
     *
     * @param  string  $table
     * @param  bool    $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        if (!is_null($table) && $create) {
            return $this->files->get(__DIR__.'/stubs/migration.stub');
        }

        return parent::getStub($table, $create);
    }
}
