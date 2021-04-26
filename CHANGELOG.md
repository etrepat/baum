## 1.1.1
_Mar 4 2015_
* bug fixes
  - Fix README.md typos [PR #134 and #131]
  - Use table prefixes in `rebuild` command [PR #133]
  - Fix `limitDepth` query scope [PR #125]

## 1.1.0
_Mar 3 2015_
* enhancements
  - Add support for Laravel 5.x, leave a [1.0.x](https://github.com/etrepat/baum/tree/1.0.x-stable) branch for backwards compatibility.

## 1.0.13
_Sep 30 2014_
* bug fixes
  - Fix node depth recomputation when moving nodes. Closes [#109] & [#106].
  - Laravel 5, composer compatibility.

## 1.0.12
_Aug 25 2014_
* bug fixes
  - Fix check for `Node->storeNewParent()` to avoid moving a node which was already root. Fixes [#79] and closes [#81].
  - Several git fixes by @GrahamCampbell [PR #96].
  - `.travis.yml` improvements by @GrahamCampbell [PR #95].
  - Rename `isValid` static method to `isValidNestedSet` to avoid name clashes with validator packages. Closes [#89].
  - Moving to root has been now reimplemented to be more consistent.

* enhancements
  - Add pessimistic locking into the library. Closes [#89].
  - Add `trunks` family of methods: `allTrunks`, `trunks`, `getTrunks` and `isTrunk`. Closes [#59].
  - Add a `force` option to the `rebuild` static method.

## 1.0.11
_Jul 3 2014_
* bug fixes
  - Target Laravel stable version in `composer.json` file. Merges [#67].
  - Use qualified order column name on `newNestedSetQuery` method. Merges [#70].
  - Cleaned up migration stub. Merges [#84].
  - Enforce sorting for `$orderColumn` when calling `toHierarchy`. Merges [#73], fixing [#71].

* enhancements
  - [#77] Add `makeFirstChildOf` and `makeLastChildOf` helper methods.
  - [#62] Implement `limitDepth` query scope to allow query depth limiting for huge descendancy chains. Also allow to pass the depth limit as the first parameter of `getDescendants` and `getDescendantsAndSelf`.
  - [#67] Should work with Laravel 4.2
  - [#68] Implement `buildTree`, `makeTree` mass-assignment (seeding) methods.

## 1.0.10
_May 2 2014_
* bug fixes
  - Fix inserting a new model with a defined scope. Fixes [#27].
  - Static methods now do not take into account scoped column values, which
  would not make sense.
  - Properly set a model's relations when reloading an instance via `reload()`.
  - Fix `getObservableEvents()` function to include node's `moving` and `moved`
  events.
  - Fix `reload()` to consider trashed node objects via soft-delete operations.
  Fixes [#35].
  - Preliminar support for soft-delete operations. Should fix [#23].
  - Assigning `null` to the `parent_id` column value and saving the node now
  performs the same operation as `makeRoot()`. Fixes [#54].
  - Reimplement `toHierarchy` as yielded inconsistent results, even worse with custom sorting of the collection results. Merges [#61], fixes [#63].

* enhancements
  - Implement tree *structure validation* via `Node::isValid`.
  - Implement tree *index rebuilding* via `Node::rebuild`. Very useful when a tree
  structure has been messed up or to convert from a `parent_id` only table.
  - Preliminar support for soft-delete operations.
  - Allow the user to change the default sorting column name (defaulting to `lft`).
  - Add support for non-numeric keys. Merges & fixes [#52].


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
