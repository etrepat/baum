<?php
namespace Baum;

use Illuminate\Database\Eloquent\Collection as Eloquent_Collection;

class Collection extends Eloquent_Collection {

	public function toHierarchical(){
		$tree = $this->items;
		return new Eloquent_Collection($this->hierarchical($tree));
	}


    private function hierarchical(&$result) {
	    $new = array();
	    if(is_array($result)) {
	        while(list($n, $sub) = each($result)) {
	        	$new[$sub->getKey()] = $sub;
	            if($sub->getRight() - $sub->getLeft() != 1) {
	                $new[$sub->getKey()]->children = $this->hierarchical($result);
	            }
	            $next_id = key($result);
	            if($next_id && $result[$next_id]->getParentId() != $sub->getParentId()) {
	                return $new;
	            }
	        }
	    }
	    return $new;
	}
}