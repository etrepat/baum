# Baum

[![Build Status](https://travis-ci.org/etrepat/baum.png?branch=master)](https://travis-ci.org/etrepat/baum)

Baum is an implementation of the [Nested Set](http://en.wikipedia.org/wiki/Nested_set_model)
pattern for [Laravel 4's](http://laravel.com/) Eloquent ORM.

## Documentation

* [About Nested Sets](#about)
* [The theory behind, a TL;DR version](#theory)
* [Installation](#installation)
* [Getting started](#getting-started)
* [Usage](#usage)

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

Baum works with Laravel 4 onwards. You can add it to your `composer.json` file
with:

    "baum/baum": "~1.0"

Run `composer install` to install it.

As with most Laravel 4 packages you'll then need to register the Baum
*service provider*. To do that, head over your `app/config/app.php` file and add
the following line into the `providers` array:

    'Baum\BaumServiceProvider',

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
* [Model events: `moving` and `moved`](#node-model-events)

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
* `makeRoot()`: Make current node a root node.

For example:

```php
$root = Creatures::create(['name' => 'The Root of All Evil']);

$dragons = Creatures::create(['name' => 'Here Be Dragons']);
$dragons->makeChildOf($root);

$monsters = new Creatures(['name' => 'Horrible Monsters']);
$monsters->save();

$monsters-makeSiblingOf($dragons);

$demons = Creatures::where('name', '=', 'demons');
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
* `insideSubtree($node)`: Checks wether the given node is inside the subtree
defined by the left and right indices.

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

Second, as **methods** which return actual `Baum\Node` instances.

* `getRoot()`: Returns the root node starting at the current node.
* `getAncestorsAndSelf()`: Retrieve all of the ancestor chain including the current node.
* `getAncestors()`: Get all of the ancestor chain from the database excluding the current node.
* `getSiblingsAndSelf()`: Get all children of the parent, including self.
* `getSiblings()`: Return all children of the parent, except self.
* `getLeaves()`: Return all of its nested children which do not have children.
* `getDescendantsAndSelf()`: Retrieve all nested children and self.
* `getDescendants()`: Retrieve all of its children & nested children.
* `getImmediateDescendants()`: Retrieve all of its children nodes (non-recursive).

Here's a simple example for iterating a node's descendants (provided a name
attribute is available):

```php
$node = Category::where('name', '=', 'Books');

foreach($node->getDescendantsAndSelf() as $descendant) {
  echo "{$descendant->name}";
}
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

## TODO

Some things I'm probably adding to this library (soonish, I hope):

1. Scoping support. As of now, there's no scoping involved in Baum's queries.
Introducing scoping support, and some way to configure it, will allow us
to have various Nested Set models in the same database table.
2. Rebuild from other implementations. You've got a current model & table,
with only a `parent_id` id column? Shouldn't be a problem. Should it?

## Contributing

Thinking of contributing? Maybe you've found some nasty bug? That's great news!

1. Fork the project:.
2. Create your bugfix/feature branch.
3. Commit your changes & push to the branch.
4. Create new Pull Request

## License

Baum is licensed under the terms of the [MIT License](http://opensource.org/licenses/MIT)
(See LICENSE file for details).

---

Coded by [Estanislau Trepat (etrepat)](http://etrepat.com). I'm also
[@etrepat](http://twitter.com/etrepat) on twitter.
