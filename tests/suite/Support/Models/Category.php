<?php

namespace Baum\Tests\Support\Models;

use Baum\Node;

class Category extends Node
{
    protected $table = 'categories';

    protected $fillable = ['name'];

    public $timestamps = false;
}
