<?php

namespace Baum\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Baum\NestedSet\Node;

class Category extends Model
{
    use Node;

    protected $table = 'categories';

    protected $fillable = ['name'];

    public $timestamps = false;
}
