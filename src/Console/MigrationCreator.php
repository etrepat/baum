<?php

namespace Baum\Console;

use Illuminate\Database\Migrations\MigrationCreator as BaseMigrationCreator;

class MigrationCreator extends BaseMigrationCreator
{
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
