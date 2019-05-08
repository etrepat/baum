<?php

namespace Baum\Tests\Support\Models;

class ScopedCategory extends Category
{
    protected $fillable = ['name', 'company_id'];

    protected $scopeColumnNames = ['company_id'];
}
