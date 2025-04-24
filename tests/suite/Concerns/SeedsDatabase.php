<?php

namespace Baum\Tests\Concerns;

trait SeedsDatabase
{
    public function seed($seeder)
    {
        if (!is_string($seeder)) {
            $seeder = get_class($seeder);
        }

        with(new $seeder)->run();
    }
}
