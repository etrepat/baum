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
