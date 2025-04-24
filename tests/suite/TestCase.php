<?php

namespace Baum\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

use Baum\Tests\Support\Migrators\CategoryMigrator;
use Baum\Tests\Support\Migrators\ClusterMigrator;
use Baum\Tests\Support\Seeders\CategorySeeder;
use Baum\Tests\Support\Seeders\ClusterSeeder;
use Baum\Tests\Support\Models\Category;
use Baum\Tests\Support\Models\Cluster;

class TestCase extends BaseTestCase
{
    use Concerns\MigratesDatabase;
    use Concerns\SeedsDatabase;

    public function setUp(): void
    {
        $this->migrate(CategoryMigrator::class);

        $this->migrate(ClusterMigrator::class);

        $this->seed(CategorySeeder::class);

        $this->seed(ClusterSeeder::class);
    }

    protected function findByName(string $name, $klass = Category::class)
    {
        $instance = new $klass;

        return $instance->newQueryWithoutScopes()->where('name', '=', $name)->first();
    }

    protected function categories($name, $klass = Category::class)
    {
        return $this->findByName($name, $klass);
    }

    protected function clusters($name, $klass = Cluster::class)
    {
        return $this->findByName($name, $klass);
    }
}
