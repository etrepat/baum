<?php

namespace Baum;

use Illuminate\Database\Eloquent\Model;

use Baum\NestedSet\Node as HasNestedSetProperties;

/**
 * Node
 *
 * This abstract class implements Nested Set functionality. A Nested Set is a
 * smart way to implement an ordered tree with the added benefit that you can
 * select all of their descendants with a single query. Drawbacks are that
 * insertion or move operations need more complex sql queries.
 *
 * Nested sets are appropiate when you want either an ordered tree (menus,
 * commercial categories, etc.) or an efficient way of querying big trees.
 *
 * DEPRECATION NOTICE: For now, this class is left as a wrapper layer and for
 * compatibility purposes. It is *strongly* recommended to use the _trait_ based
 * interface in your own applications, ie: similarly as it is used
 * here.
 */
abstract class Node extends Model
{
    use HasNestedSetProperties;
}
