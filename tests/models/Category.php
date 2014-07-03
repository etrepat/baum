<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Baum\Node;

class Category extends Node {

  protected $table = 'categories';

  public $timestamps = false;

}

class ScopedCategory extends Category {

  protected $scoped = array('company_id');

}

class MultiScopedCategory extends Category {

  protected $scoped = array('company_id', 'language');

}

class OrderedCategory extends Category {

  protected $orderColumn = 'name';

}

class OrderedScopedCategory extends Category {

  protected $scoped = array('company_id');

  protected $orderColumn = 'name';

}

class SoftCategory extends Category {

  use SoftDeletingTrait;

  public $timestamps = true;

  protected $dates = ['deleted_at'];

}
