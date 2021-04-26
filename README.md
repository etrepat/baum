# Baum

[![Build Status](https://travis-ci.org/etrepat/baum.png?branch=master)](https://travis-ci.org/etrepat/baum)

Baum is an implementation of the [Nested Set](http://en.wikipedia.org/wiki/Nested_set_model) pattern for [Laravel 5's](http://laravel.com/) Eloquent ORM.

> For **Laravel 4.2.x compatibility**, check the [1.0.x branch](https://github.com/etrepat/baum/tree/1.0.x-stable) branch or use the latest [1.0.x tagged release](https://github.com/etrepat/baum/releases).

## Documentation

* [About Nested Sets](#about)
* [The theory behind, a TL;DR version](#theory)
* [Installation](#installation)
* [Getting started](#getting-started)
* [Usage](#usage)
* [Further information](#further-information)
* [Contributing](#contributing)

<a name="about"></a>
## About Nested Sets

A nested set is a smart way to implement an _ordered_ tree that allows for fast,
non-recursive queries. For example, you can fetch all descendants of a node in a
single query, no matter how deep the tree. The drawback is that insertions/moves/deletes
require complex SQL, but that is handled behind the curtains by this package!

Nested sets are appropriate for ordered trees (e.g. menus, commercial categories)
and big trees that must be queried efficiently (e.g. threaded posts).

See the [wikipedia entry for nested sets](http://en.wikipedia.org/wiki/Nested_set_model)
for more info. Also, this is a good introductory tutorial:
[http://www.evanpetersen.com/item/nested-sets.html](http://www.evanpetersen.com/item/nested-sets.html)

<a name="theory"></a>
## The theory behind, a TL;DR version

An easy way to visualize how a nested set works is to think of a parent entity surrounding all
of its children, and its parent surrounding it, etc. So this tree:

    root
      |_ Child 1
        |_ Child 1.1
        |_ Child 1.2
      |_ Child 2
        |_ Child 2.1
        |_ Child 2.2


Could be visualized like this:

     ___________________________________________________________________
    |  Root                                                             |
    |    ____________________________    ____________________________   |
    |   |  Child 1                  |   |  Child 2                  |   |
    |   |   __________   _________  |   |   __________   _________  |   |
    |   |  |  C 1.1  |  |  C 1.2 |  |   |  |  C 2.1  |  |  C 2.2 |  |   |
    1   2  3_________4  5________6  7   8  9_________10 11_______12 13  14
    |   |___________________________|   |___________________________|   |
    |___________________________________________________________________|

The numbers represent the left and right boundaries.  The table then might
look like this:

    id | parent_id | lft  | rgt  | depth | data
     1 |           |    1 |   14 |     0 | root
     2 |         1 |    2 |    7 |     1 | Child 1
     3 |         2 |    3 |    4 |     2 | Child 1.1
     4 |         2 |    5 |    6 |     2 | Child 1.2
     5 |         1 |    8 |   13 |     1 | Child 2
     6 |         5 |    9 |   10 |     2 | Child 2.1
     7 |         5 |   11 |   12 |     2 | Child 2.2

To get all children of a _parent_ node, you

```sql
SELECT * WHERE lft IS BETWEEN parent.lft AND parent.rgt
```

To get the number of children, it's

```sql
(right - left - 1)/2
```

To get a node and all its ancestors going back to the root, you

```sql
SELECT * WHERE node.lft IS BETWEEN lft AND rgt
```

As you can see, queries that would be recursive and prohibitively slow on
ordinary trees are suddenly quite fast. Nifty, isn't it?

<a name="installation"></a>
## Installation

Baum works with Laravel 5 onwards. You can add it to your `composer.json` file
with:

    "baum/baum": "~1.1"

Run `composer install` to install it.

As with most Laravel 5 packages you'll then need to register the Baum
*service provider*. To do that, head over your `config/app.php` file and add
the following line into the `providers` array:

    'Baum\Providers\BaumServiceProvider',

<a name="getting-started"></a>
## Getting started

After the package is correctly installed the easiest way to get started is to
run the provided generator:

    php artisan baum:install MODEL

Replace model by the class name you plan to use for your Nested Set model.

The generator will install a migration and a model file into your application
configured to work with the Nested Set behaviour provided by Baum. You SHOULD
take a look at those files, as each of them describes how they can be customized.

Next, you would probably run `artisan migrate` to apply the migration.

### Model configuration

In order to work with Baum, you must ensure that your model class extends
`Baum\Node`.

This is the easiest it can get:

```php
class Category extends Baum\Node {

}
```

This is a *slightly* more complex example where we have the column names customized:

```php
class Dictionary extends Baum\Node {

  protected $table = 'dictionary';

  // 'parent_id' column name
  protected $parentColumn = 'parent_id';

  // 'lft' column name
  protected $leftColumn = 'lidx';

  // 'rgt' column name
  protected $rightColumn = 'ridx';

  // 'depth' column name
  protected $depthColumn = 'nesting';

  // guard attributes from mass-assignment
  protected $guarded = array('id', 'parent_id', 'lidx', 'ridx', 'nesting');

}
```

Remember that, obviously, the column names must match those in the database table.

### Migration configuration

You must ensure that the database table that supports your Baum models has the
following columns:

* `parent_id`: a reference to the parent (int)
* `lft`: left index bound (int)
* `rgt`: right index bound (int)
* `depth`: depth or nesting level (int)

Here is a sample migration file:

```php
class Category extends Migration {

  public function up() {
    Schema::create('categories', function(Blueprint $table) {
      $table->increments('id');

      $table->integer('parent_id')->nullable();
      $table->integer('lft')->nullable();
      $table->integer('rgt')->nullable();
      $table->integer('depth')->nullable();

      $table->string('name', 255);

      $table->timestamps();
    });
  }

  public function down() {
    Schema::drop('categories');
  }

}
```

You may freely modify the column names, provided you change them both in the
migration and the model.

<a name="usage"></a>
## Usage

After you've configured your model and run the migration, you are now ready
to use Baum with your model. Below are some examples.

* [Creating a root node](#creating-root-node)
* [Inserting nodes](#inserting-nodes)
* [Deleting nodes](#deleting-nodes)
* [Getting the nesting level of a node](#node-level)
* [Moving nodes around](#moving-nodes)
* [Asking questions to your nodes](#node-questions)
* [Relations](#node-relations)
* [Root and Leaf scopes](#node-basic-scopes)
* [Accessing the ancestry/descendancy chain](#node-chains)
* [Limiting levels of children returned](#limiting-depth)
* [Custom sorting column](#custom-sorting-column)
* [Dumping the hierarchy tree](#hierarchy-tree)
* [Model events: `moving` and `moved`](#node-model-events)
* [Scope support](#scope-support)
* [Validation](#validation)
* [Tree rebuilding](#rebuilding)
* [Soft deletes](#soft-deletes)
* [Seeding/Mass assignment](#seeding)
* [Misc/Utility functions](#misc-utilities)

<a name="creating-root-node"></a>
### Creating a root node

By default, all nodes are created as roots:

```php
$root = Category::create(['name' => 'Root category']);
```

Alternatively, you may find yourself in the need of *converting* an existing node
into a *root node*:

```php
$node->makeRoot();
```

You may also nullify it's `parent_id` column to accomplish the same behaviour:

```php
// This works the same as makeRoot()
$node->parent_id = null;
$node->save();
```

<a name="inserting-nodes"></a>
### Inserting nodes

```php
// Directly with a relation
$child1 = $root->children()->create(['name' => 'Child 1']);

// with the `makeChildOf` method
$child2 = Category::create(['name' => 'Child 2']);
$child2->makeChildOf($root);
```

<a name="deleting-nodes"></a>
### Deleting nodes

```php
$child1->delete();
```

Descendants of deleted nodes will also be deleted and all the `lft` and `rgt`
bound will be recalculated. Pleases note that, for now, `deleting` and `deleted`
model events for the descendants will not be fired.

<a name="node-level"></a>
### Getting the nesting level of a node

The `getLevel()` method will return current nesting level, or depth, of a node.

```php
$node->getLevel() // 0 when root
```

<a name="moving-nodes"></a>
### Moving nodes around

Baum provides several methods for moving nodes around:

* `moveLeft()`: Find the left sibling and move to the left of it.
* `moveRight()`: Find the right sibling and move to the right of it.
* `moveToLeftOf($otherNode)`: Move to the node to the left of ...
* `moveToRightOf($otherNode)`: Move to the node to the right of ...
* `makeNextSiblingOf($otherNode)`: Alias for `moveToRightOf`.
* `makeSiblingOf($otherNode)`: Alias for `makeNextSiblingOf`.
* `makePreviousSiblingOf($otherNode)`: Alias for `moveToLeftOf`.
* `makeChildOf($otherNode)`: Make the node a child of ...
* `makeFirstChildOf($otherNode)`: Make the node the first child of ...
* `makeLastChildOf($otherNode)`: Alias for `makeChildOf`.
* `makeRoot()`: Make current node a root node.

For example:

```php
$root = Creatures::create(['name' => 'The Root of All Evil']);

$dragons = Creatures::create(['name' => 'Here Be Dragons']);
$dragons->makeChildOf($root);

$monsters = new Creatures(['name' => 'Horrible Monsters']);
$monsters->save();

$monsters->makeSiblingOf($dragons);

$demons = Creatures::where('name', '=', 'demons')->first();
$demons->moveToLeftOf($dragons);
```

<a name="node-questions"></a>
### Asking questions to your nodes

You can ask some questions to your Baum nodes:

* `isRoot()`: Returns true if this is a root node.
* `isLeaf()`: Returns true if this is a leaf node (end of a branch).
* `isChild()`: Returns true if this is a child node.
* `isDescendantOf($other)`: Returns true if node is a descendant of the other.
* `isSelfOrDescendantOf($other)`: Returns true if node is self or a descendant.
* `isAncestorOf($other)`: Returns true if node is an ancestor of the other.
* `isSelfOrAncestorOf($other)`: Returns true if node is self or an ancestor.
* `equals($node)`: current node instance equals the other.
* `insideSubtree($node)`: Checks whether the given node is inside the subtree
defined by the left and right indices.
* `inSameScope($node)`: Returns true if the given node is in the same scope
as the current one. That is, if *every* column in the `scoped` property has
the same value in both nodes.

Using the nodes from the previous example:

```php
$demons->isRoot(); // => false

$demons->isDescendantOf($root) // => true
```

<a name="node-relations"></a>
### Relations

Baum provides two self-referential Eloquent relations for your nodes: `parent`
and `children`.

```php
$parent = $node->parent()->get();

$children = $node->children()->get();
```

<a name="node-basic-scopes"></a>
### Root and Leaf scopes

Baum provides some very basic query scopes for accessing the root and leaf nodes:

```php
// Query scope which targets all root nodes
Category::roots()

// All leaf nodes (nodes at the end of a branch)
Category:allLeaves()
```

You may also be interested in only the first root:

```php
$firstRootNode = Category::root();
```

<a name="node-chains"></a>
### Accessing the ancestry/descendancy chain

There are several methods which Baum offers to access the ancestry/descendancy
chain of a node in the Nested Set tree. The main thing to keep in mind is that
they are provided in two ways:

First as **query scopes**, returning an `Illuminate\Database\Eloquent\Builder`
instance to continue to query further. To get *actual* results from these,
remember to call `get()` or `first()`.

* `ancestorsAndSelf()`: Targets all the ancestor chain nodes including the current one.
* `ancestors()`: Query the ancestor chain nodes excluding the current one.
* `siblingsAndSelf()`: Instance scope which targets all children of the parent, including self.
* `siblings()`: Instance scope targeting all children of the parent, except self.
* `leaves()`: Instance scope targeting all of its nested children which do not have children.
* `descendantsAndSelf()`: Scope targeting itself and all of its nested children.
* `descendants()`: Set of all children & nested children.
* `immediateDescendants()`: Set of all children nodes (non-recursive).

Second, as **methods** which return actual `Baum\Node` instances (inside a `Collection`
object where appropiate):

* `getRoot()`: Returns the root node starting at the current node.
* `getAncestorsAndSelf()`: Retrieve all of the ancestor chain including the current node.
* `getAncestorsAndSelfWithoutRoot()`: All ancestors (including the current node) except the root node.
* `getAncestors()`: Get all of the ancestor chain from the database excluding the current node.
* `getAncestorsWithoutRoot()`: All ancestors except the current node and the root node.
* `getSiblingsAndSelf()`: Get all children of the parent, including self.
* `getSiblings()`: Return all children of the parent, except self.
* `getLeaves()`: Return all of its nested children which do not have children.
* `getDescendantsAndSelf()`: Retrieve all nested children and self.
* `getDescendants()`: Retrieve all of its children & nested children.
* `getImmediateDescendants()`: Retrieve all of its children nodes (non-recursive).

Here's a simple example for iterating a node's descendants (provided a name
attribute is available):

```php
$node = Category::where('name', '=', 'Books')->first();

foreach($node->getDescendantsAndSelf() as $descendant) {
  echo "{$descendant->name}";
}
```

<a name="limiting-depth"></a>
### Limiting the levels of children returned

In some situations where the hierarchy depth is huge it might be desirable to limit the number of levels of children returned (depth). You can do this in Baum by using the `limitDepth` query scope.

The following snippet will get the current node's descendants up to a maximum
of 5 depth levels below it:

```php
$node->descendants()->limitDepth(5)->get();
```

Similarly, you can limit the descendancy levels with both the `getDescendants` and `getDescendantsAndSelf` methods by supplying the desired depth limit as the first argument:

```php
// This will work without depth limiting
// 1. As usual
$node->getDescendants();
// 2. Selecting only some attributes
$other->getDescendants(array('id', 'parent_id', 'name'));
...
// With depth limiting
// 1. A maximum of 5 levels of children will be returned
$node->getDescendants(5);
// 2. A max. of 5 levels of children will be returned selecting only some attrs
$other->getDescendants(5, array('id', 'parent_id', 'name'));
```

<a name="custom-sorting-column"></a>
### Custom sorting column

By default in Baum all results are returned sorted by the `lft` index column
value for consistency.

If you wish to change this default behaviour you need to specify in your model
the name of the column you wish to use to sort your results like this:

```php
protected $orderColumn = 'name';
```

<a name="hierarchy-tree"></a>
### Dumping the hierarchy tree

Baum extends the default `Eloquent\Collection` class and provides the
`toHierarchy` method to it which returns a nested collection representing the
queried tree.

Retrieving a complete tree hierarchy into a regular `Collection` object with
its children *properly nested* is as simple as:

```php
$tree = Category::where('name', '=', 'Books')->first()->getDescendantsAndSelf()->toHierarchy();
```

<a name="node-model-events"></a>
### Model events: `moving` and `moved`

Baum models fire the following events: `moving` and `moved` every time a node
is *moved* around the Nested Set tree. This allows you to hook into those points
in the node movement process. As with normal Eloquent model events, if `false`
is returned from the `moving` event, the movement operation will be cancelled.

The recommended way to hook into those events is by using the model's boot
method:

```php
class Category extends Baum\Node {

  public static function boot() {
    parent::boot();

    static::moving(function($node) {
      // Before moving the node this function will be called.
    });

    static::moved(function($node) {
      // After the move operation is processed this function will be
      // called.
    });
  }

}
```

<a name="scope-support"></a>
### Scope support

Baum provides a simple method to provide Nested Set "scoping" which restricts
what we consider part of a nested set tree. This should allow for multiple nested
set trees in the same database table.

To make use of the scoping funcionality you may override the `scoped` model
attribute in your subclass. This attribute should contain an array of the column
names (database fields) which shall be used to restrict Nested Set queries:

```php
class Category extends Baum\Node {
  ...
  protected $scoped = array('company_id');
  ...
}
```

In the previous example, `company_id` effectively restricts (or "scopes") a
Nested Set tree. So, for each value of that field we may be able to construct
a full different tree.

```php
$root1 = Category::create(['name' => 'R1', 'company_id' => 1]);
$root2 = Category::create(['name' => 'R2', 'company_id' => 2]);

$child1 = Category::create(['name' => 'C1', 'company_id' => 1]);
$child2 = Category::create(['name' => 'C2', 'company_id' => 2]);

$child1->makeChildOf($root1);
$child2->makeChildOf($root2);

$root1->children()->get(); // <- returns $child1
$root2->children()->get(); // <- returns $child2
```

All methods which ask or traverse the Nested Set tree will use the `scoped`
attribute (if provided).

**Please note** that, for now, moving nodes between scopes is not supported.

<a name="validation"></a>
### Validation

The `::isValidNestedSet()` static method allows you to check if your underlying tree structure is correct. It mainly checks for these 3 things:

* Check that the bound indexes `lft`, `rgt` are not null, `rgt` values greater
than `lft` and within the bounds of the parent node (if set).
* That there are no duplicates for the `lft` and `rgt` column values.
* As the first check does not actually check root nodes, see if each root has
the `lft` and `rgt` indexes within the bounds of its children.

All of the checks are *scope aware* and will check each scope separately if needed.

Example usage, given a `Category` node class:

```php
Category::isValidNestedSet()
=> true
```

<a name="rebuilding"></a>
### Tree rebuilding

Baum supports for complete tree-structure rebuilding (or reindexing) via the
`::rebuild()` static method.

This method will re-index all your `lft`, `rgt` and `depth` column values,
inspecting your tree only from the parent <-> children relation
standpoint. Which means that you only need a correctly filled `parent_id` column
and Baum will try its best to recompute the rest.

This can prove quite useful when something has gone horribly wrong with the index
values or it may come quite handy when *converting* from another implementation
(which would probably have a `parent_id` column).

This operation is also *scope aware* and will rebuild all of the scopes
separately if they are defined.

Simple example usage, given a `Category` node class:

```php
Category::rebuild()
```

Valid trees (per the `isValidNestedSet` method) will not get rebuilt. To force the index rebuilding process simply call the rebuild method with `true` as the first parameter:

```php
Category::rebuild(true);
```

<a name="soft-deletes"></a>
### Soft deletes

Baum comes with **limited support** for soft-delete operations. What I mean
by *limited* is that the testing is still limited and the *soft delete*
functionality is changing in the upcoming 4.2 version of the framework, so use
this feature wisely.

For now, you may consider a **safe** `restore()` operation to be one of:

* Restoring a leaf node
* Restoring a whole sub-tree in which the parent is not soft-deleted

<a name="seeding"></a>
### Seeding/Mass-assignment

Because Nested Set structures usually involve a number of method calls to build a hierarchy structure (which result in several database queries), Baum provides two convenient methods which will map the supplied array of node attributes and create a hierarchy tree from them:

* `buildTree($nodeList)`: (static method) Maps the supplied array of node attributes into the database.
* `makeTree($nodeList)`: (instance method) Maps the supplied array of node attributes into the database using the current node instance as the parent for the provided subtree.

Both methods will *create* new nodes when the primary key is not supplied, *update* or *create* if it is, and *delete* all nodes which are not present in the *affecting scope*. Understand that the *affecting scope* for the `buildTree` static method is the whole nested set tree and for the `makeTree` instance method are all of the current node's descendants.

For example, imagine we wanted to map the following category hierarchy into our database:

- TV & Home Theater
- Tablets & E-Readers
- Computers
  + Laptops
    * PC Laptops
    * Macbooks (Air/Pro)
  + Desktops
  + Monitors
- Cell Phones

This could be easily accomplished with the following code:

```php
$categories = [
  ['id' => 1, 'name' => 'TV & Home Theather'],
  ['id' => 2, 'name' => 'Tablets & E-Readers'],
  ['id' => 3, 'name' => 'Computers', 'children' => [
    ['id' => 4, 'name' => 'Laptops', 'children' => [
      ['id' => 5, 'name' => 'PC Laptops'],
      ['id' => 6, 'name' => 'Macbooks (Air/Pro)']
    ]],
    ['id' => 7, 'name' => 'Desktops'],
    ['id' => 8, 'name' => 'Monitors']
  ]],
  ['id' => 9, 'name' => 'Cell Phones']
];

Category::buildTree($categories) // => true
```

After that, we may just update the hierarchy as needed:

```php
$categories = [
  ['id' => 1, 'name' => 'TV & Home Theather'],
  ['id' => 2, 'name' => 'Tablets & E-Readers'],
  ['id' => 3, 'name' => 'Computers', 'children' => [
    ['id' => 4, 'name' => 'Laptops', 'children' => [
      ['id' => 5, 'name' => 'PC Laptops'],
      ['id' => 6, 'name' => 'Macbooks (Air/Pro)']
    ]],
    ['id' => 7, 'name' => 'Desktops', 'children' => [
      // These will be created
      ['name' => 'Towers Only'],
      ['name' => 'Desktop Packages'],
      ['name' => 'All-in-One Computers'],
      ['name' => 'Gaming Desktops']
    ]]
    // This one, as it's not present, will be deleted
    // ['id' => 8, 'name' => 'Monitors'],
  ]],
  ['id' => 9, 'name' => 'Cell Phones']
];

Category::buildTree($categories); // => true
```

The `makeTree` instance method works in a similar fashion. The only difference
is that it will only perform operations on the *descendants* of the calling node instance.

So now imagine we already have the following hierarchy in the database:

- Electronics
- Health Fitness & Beaty
- Small Appliances
- Major Appliances

If we execute the following code:

```php
$children = [
  ['name' => 'TV & Home Theather'],
  ['name' => 'Tablets & E-Readers'],
  ['name' => 'Computers', 'children' => [
    ['name' => 'Laptops', 'children' => [
      ['name' => 'PC Laptops'],
      ['name' => 'Macbooks (Air/Pro)']
    ]],
    ['name' => 'Desktops'],
    ['name' => 'Monitors']
  ]],
  ['name' => 'Cell Phones']
];

$electronics = Category::where('name', '=', 'Electronics')->first();
$electronics->makeTree($children); // => true
```

Would result in:

- Electronics
  + TV & Home Theater
  + Tablets & E-Readers
  + Computers
    * Laptops
      - PC Laptops
      - Macbooks (Air/Pro)
    * Desktops
    * Monitors
  + Cell Phones
- Health Fitness & Beaty
- Small Appliances
- Major Appliances

Updating and deleting nodes from the subtree works the same way.

<a name="misc-utilities"></a>
### Misc/Utility functions

#### Node extraction query scopes

Baum provides some query scopes which may be used to extract (remove) selected nodes
from the current results set.

* `withoutNode(node)`: Extracts the specified node from the current results set.
* `withoutSelf()`: Extracts itself from the current results set.
* `withoutRoot()`: Extracts the current root node from the results set.

```php
$node = Category::where('name', '=', 'Some category I do not want to see.')->first();

$root = Category::where('name', '=', 'Old boooks')->first();
var_dump($root->descendantsAndSelf()->withoutNode($node)->get());
... // <- This result set will not contain $node
```

#### Get a nested list of column values

The `::getNestedList()` static method returns a key-value pair array indicating
a node's depth. Useful for silling `select` elements, etc.

It expects the column name to return, and optionally: the column
to use for array keys (will use `id` if none supplied) and/or a separator:

```php
public static function getNestedList($column, $key = null, $seperator = ' ');
```

An example use case:

```php
$nestedList = Category::getNestedList('name');
// $nestedList will contain an array like the following:
// array(
//   1 => 'Root 1',
//   2 => ' Child 1',
//   3 => ' Child 2',
//   4 => '  Child 2.1',
//   5 => ' Child 3',
//   6 => 'Root 2'
// );
```

<a name="further-information"></a>
## Further information

You may find additional information, usage examples and/or frequently
asked questions about Baum in the [wiki](https://github.com/etrepat/baum/wiki).

Feel free to browse the wiki after finishing this README:

[https://github.com/etrepat/baum/wiki](https://github.com/etrepat/baum/wiki)

<a name="contributing"></a>
## Contributing

Thinking of contributing? Maybe you've found some nasty bug? That's great news!

1. Fork & clone the project: `git clone git@github.com:your-username/baum.git`.
2. Run the tests and make sure that they pass with your setup: `phpunit`.
3. Create your bugfix/feature branch and code away your changes. Add tests for your changes.
4. Make sure all the tests still pass: `phpunit`.
5. Push to your fork and submit new a pull request.

Please see the [CONTRIBUTING.md](https://github.com/etrepat/baum/blob/master/CONTRIBUTING.md) file for extended guidelines and/or recommendations.

## License

Baum is licensed under the terms of the [MIT License](http://opensource.org/licenses/MIT)
(See LICENSE file for details).

---

Coded by [Estanislau Trepat (etrepat)](http://etrepat.com). I'm also
[@etrepat](http://twitter.com/etrepat) on twitter.
