<?php

if ( !function_exists('hmap') ) {

  /**
   * Simple function which aids in converting the tree hierarchy into something
   * more easily testable...
   *
   * @param array   $nodes
   * @return array
   */
  function hmap(array $nodes) {
    $output = array();

    foreach($nodes as $node)
      $output[$node['name']] = empty($node['children']) ? null : hmap($node['children']);

    return $output;
  }

}
