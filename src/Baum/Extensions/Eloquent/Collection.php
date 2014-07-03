<?php
namespace Baum\Extensions\Eloquent;

use Illuminate\Database\Eloquent\Collection as BaseCollection;

class Collection extends BaseCollection {

  public function toHierarchy() {
    $dict = $this->getDictionary();

    // Enforce sorting by $orderColumn setting in Baum\Node instance
    uasort($dict, function($a, $b){
        return ($a->getOrder() >= $b->getOrder()) ? 1 : -1;
    });

    return new BaseCollection($this->hierarchical($dict));
  }

  protected function hierarchical($result) {
    foreach($result as $key => $node)
      $node->setRelation('children', new BaseCollection);

    $nestedKeys = array();

    foreach($result as $key => $node) {
      $parentKey = $node->getParentId();

      if ( !is_null($parentKey) && array_key_exists($parentKey, $result) ) {
        $result[$parentKey]->children[] = $node;

        $nestedKeys[] = $node->getKey();
      }
    }

    foreach($nestedKeys as $key)
      unset($result[$key]);

    return $result;
  }

}
