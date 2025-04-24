<?php

namespace Baum\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Baum\NestedSet\Node;

class Cluster extends Model
{
    use HasUuids, Node;

    protected $table = 'clusters';

    protected $fillable = ['name'];

    public $timestamps = false;
}
