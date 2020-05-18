<?php

namespace Baum\Tests\Support\Models;

class ScopedCluster extends Cluster
{
    protected $fillable = ['name', 'company_id'];

    protected $scopeColumnNames = ['company_id'];
}
