<?php
namespace Baum\Extensions\Eloquent;

use Illuminate\Database\Eloquent\Collection as BaseCollection;

class Collection extends BaseCollection {

  public function toHierarchy($cmp=false) {
    $tree = $this->items;

    return new BaseCollection($this->hierarchical($tree, $cmp));
  }

  protected function hierarchical(&$result, $cmp=false) {
    $new = array();

    if ( is_array($result) ) {
      while( list($n, $sub) = each($result) ) {
        $new[$sub->getKey()] = $sub;

        if ( ! $sub->isLeaf() ){
          $new[$sub->getKey()]->setRelation('children', new BaseCollection($this->hierarchical($result,$cmp)));
		}else{
			$new[$sub->getKey()]->setRelation('children',array());	
		}
        $next_id = key($result);

        if ( $next_id && $result[$next_id]->getParentId() != $sub->getParentId() ){
			if(is_callable($cmp)){
				uasort($new,$cmp);
			}
          return $new;
		}
      }
    }
	if(is_callable($cmp)){
		uasort($new,$cmp);
	}
    return $new;
  }

}
