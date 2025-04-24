<?php

namespace Baum\Tests\Support\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftCategory extends Category
{
    use SoftDeletes;

    public $timestamps = true;
}
