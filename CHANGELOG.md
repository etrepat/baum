* bug fixes
  - Fix inserting a new model with a defined scope. Fixes [#27].
  - Static methods now do not take into account scoped column values, which
  would not make sense.
  - Properly set a model's relations when reloading an instance via `reload()`.
  - Fix `getObservableEvents()` function to include node's `moving` and `moved`
  events.
  - Fix `reload()` to consider trashed node objects via soft-delete operations.
  Fixes [#35].

* enhancements
  - Implement tree *structure validation* via `Node::isValid`.
  - Implement tree *index rebuilding* via `Node::rebuild`. Very useful when a tree
  structure has been messed up or to convert from a `parent_id` only table.

## 1.0.9
_Jan 14 2014_

* bug fixes
  - [#26] Prevent impossible moves to the left or right. Now moving a node too
  further to the left or right raises a `MoveNotPossibleException`. Thanks to
  @ziadoz for spotting this issue and providing a patch.

* enhancenments
  - [#5] Implement `toHierarchy` method which returns a nested collection
  representing the queried tree. Great thanks go to @Surt for his patch on this.
  - Add a static `all` method which works as the regular `Eloquent\Model::all`
  method but sorts for the `lft` column.

## 1.0.8
_Oct 11 2013_

* bug fixes
  - Properly wrap column names when used inside raw queries.
  - Correct examples from README.

## 1.0.6
_July 31 2013_

* enhancements
  - Implement `withoutNode`, `withoutRoot`, `withoutSelf` query scopes.
  - [#15] Add `Node::getNestedList` static method thanks to @gerp.

## 1.0.5
_July 22 2013_

* enhancements
  - Implement simple means to implement "scopes" for a Nested Set tree, allowing
  for multiple trees in the same database table.

## 1.0.4
_July 18 2013_

* bug fixes
  - Full support for PHP >= 5.3.7. Baum should work if Eloquent works.

## 1.0.3
_July 5th 2013_

* enhanments
  - Add support for PHP 5.3 to make Baum match the requirements of Eloquent.
  - Improve Postgres friendliness

* bug fixes
  - `insideSubtree` was inside incorrectly to guard against impossible moves. Fixed.
  - Due to default ordering by `lft` column, Postgres complained when using
  aggregate methods on queries which had SORT BY clauses. Now those are pruned
  before running aggregates.


## 1.0.2
_July 3rd 2013_

* enhancements
  - Add `immediateDescendants`, 'getImmediateDescendants' methods.
  - Implement a test suite.

* bug fixes
  - [#8] Add `lft` logic ordering by default to every scope method. Thanks to
  @dirkpostma for spotting this out.
  - [#9], [#10] Fix an SQL generation bug when moving root nodes between them.
  Thanks to @daxborges for providing the fix.
  - Fix `getLeftSibling` method which was not behaving properly because of
  the previosly stated bugs.

## 1.0.1
_May 7th 2013_

* enhancements
  - Add indexes to `parent_id`, `lft`, `rgt` columns by default on generated
  migration.

* bug fix
  - [#1] Fix a bug which caused model events not to fire on subclassed instances
  of `Baum\Node`.


## 1.0.0
_May 4th 2013_

* First release.
