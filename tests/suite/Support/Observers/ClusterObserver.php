<?php

namespace Baum\Tests\Support\Observers;

use Baum\Tests\Support\Models\Cluster;

class ClusterObserver
{
    /**
     * "Creating" model event.
     *
     * @param \Baum\Tests\Support\Models\Cluster $cluster
     * @return void
     */
    public function creating(Cluster $cluster)
    {
        $cluster->ensureUuid();
    }
}
