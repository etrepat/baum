<?php

namespace Baum\Tests\Support\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftCluster extends Cluster
{
    use SoftDeletes;

    public $timestamps = true;
}
