<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Baum\Node;

class Rank extends Node {

  use SoftDeletingTrait;

  protected $table = 'ranks';

  protected $dates = ['deleted_at'];

}
