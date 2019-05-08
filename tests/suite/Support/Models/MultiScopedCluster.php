<?php

namespace Baum\Tests\Support\Models;

class MultiScopedCluster extends Cluster
{
    protected $fillable = ['name', 'company_id', 'language'];

    protected $scopeColumnNames = ['company_id', 'language'];
}
