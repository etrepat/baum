<?php

namespace Baum\Tests\Support\Models;

class MultiScopedCategory extends Category
{
    protected $fillable = ['name', 'company_id', 'language'];

    protected $scopeColumnNames = ['company_id', 'language'];
}
