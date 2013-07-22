<?php

use Baum\Node;

class ScopedCategory extends Node {

  protected $table = 'categories';

  protected $scoped = array('company_id');

  public $timestamps = false;

}
