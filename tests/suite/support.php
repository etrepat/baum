<?php

if ( !function_exists('hmap') ) {

  /**
   * Simple function which aids in converting the tree hierarchy into something
   * more easily testable...
   *
   * @param array   $nodes
   * @return array
   */
  function hmap(array $nodes, $preserve = null) {
    $output = array();

    foreach($nodes as $node) {
      if ( is_null($preserve) ) {
        $output[$node['name']] = empty($node['children']) ? null : hmap($node['children']);
      } else {
        $preserve = is_string($preserve) ? array($preserve) : $preserve;

        $current = \Illuminate\Support\Arr::only($node, $preserve);
        if ( array_key_exists('children', $node) ) {
          $children = $node['children'];

          if ( count($children) > 0 )
            $current['children'] = hmap($children, $preserve);
        }

        $output[] = $current;
      }
    }

    return $output;
  }

}

if ( !function_exists('array_ints_keys') ) {

  /**
   * Cast provided keys's values into ints. This is to wrestle with PDO driver
   * inconsistencies.
   *
   * @param   array $input
   * @param   mixed $keys
   * @return  array
   */
  function array_ints_keys(array $input, $keys='id') {
    $keys = is_string($keys) ? array($keys) : $keys;

    array_walk_recursive($input, function(&$value, $key) use ($keys) {
      if ( array_search($key, $keys) !== false )
        $value = (int) $value;
    });

    return $input;
  }

}
