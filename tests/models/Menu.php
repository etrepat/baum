<?php

use Baum\Node;

class Menu extends Node {

  protected $table = 'menus';

  protected $scoped = array('site_id', 'language');

  public $timestamps = false;

}
