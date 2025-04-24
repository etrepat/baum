<?php

namespace Baum\Tests\Concerns;

trait MigratesDatabase
{
    public function migrate($migrator)
    {
        if (!is_string($migrator)) {
            $migrator = get_class($migrator);
        }

        with(new $migrator)->up();
    }
}
