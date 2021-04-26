<?php

use Illuminate\Database\Eloquent\SoftDeletes;
use Baum\Node;

class Cluster extends Node {

  protected $table = 'clusters';

  public $incrementing = false;

  public $timestamps = false;

  protected static function boot() {
    parent::boot();

    static::creating(function($cluster) {
      $cluster->ensureUuid();
    });
  }

  public function ensureUuid() {
    if ( is_null($this->getAttribute($this->getKeyName())) )
      $this->setAttribute($this->getKeyName(), $this->generateUuid());

    return $this;
  }

  protected function generateUuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }

}

class ScopedCluster extends Cluster {

  protected $scoped = array('company_id');

}

class MultiScopedCluster extends Cluster {

  protected $scoped = array('company_id', 'language');

}

class OrderedCluster extends Cluster {

  protected $orderColumn = 'name';

}

class SoftCluster extends Cluster {

  use SoftDeletes;

  public $timestamps = true;

  protected $dates = ['deleted_at'];

}
