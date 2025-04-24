<?php

namespace Baum\Tests\Support\Models;

class OrderedScopedCategory extends Category
{
    protected $fillable = ['name', 'company_id'];

    protected $scopeColumnNames = ['company_id'];

    protected $orderColumnName = 'name';
}
