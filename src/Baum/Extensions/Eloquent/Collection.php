<?php
namespace Baum\Extensions\Eloquent;

use Illuminate\Database\Eloquent\Collection as BaseCollection;

class Collection extends BaseCollection {

  public function toHierarchy() {
    $tree = $this->items;

    return new BaseCollection($this->hierarchical($tree));
  }

  protected function hierarchical(&$result) {
    $new = array();

    if ( is_array($result) ) {
      while( list($n, $sub) = each($result) ) {
        $new[$sub->getKey()] = $sub;

        if ( ! $sub->isLeaf() )
          $new[$sub->getKey()]->setRelation('children', new BaseCollection($this->hierarchical($result)));

        $next_id = key($result);

        if ( $next_id && $result[$next_id]->getParentId() != $sub->getParentId() )
          return $new;
      }
    }

    return $new;
  }

}
