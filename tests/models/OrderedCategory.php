<?php

use Baum\Node;

class OrderedCategory extends Node {

  protected $table = 'categories';

  protected $orderColumn = 'name';

  public $timestamps = false;

}
