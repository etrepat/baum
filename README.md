# Baum

Baum is an implementation of the [Nested Set](http://en.wikipedia.org/wiki/Nested_set_model)
pattern for [Laravel 4's](http://laravel.com/) Eloquent ORM.

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
     1 |           |    1 |   14 |     0 |root
     2 |         1 |    2 |    7 |     1 | Child 1
     3 |         2 |    3 |    4 |     2 | Child 1.1
     4 |         2 |    5 |    6 |     2 | Child 1.2
     5 |         1 |    8 |   13 |     1 | Child 2
     6 |         5 |    9 |   10 |     2 | Child 2.1
     7 |         5 |   11 |   12 |     2 | Child 2.2

To get all children of a _parent_ node, you

    SELECT * WHERE lft IS BETWEEN parent.lft AND parent.rgt

To get the number of children, it's

    (right - left - 1)/2

To get a node and all its ancestors going back to the root, you

    SELECT * WHERE node.lft IS BETWEEN lft AND rgt

As you can see, queries that would be recursive and prohibitively slow on
ordinary trees are suddenly quite fast. Nifty, isn't it?

## Installation

TODO

## Laravel & Eloquent Node configuration

TODO

## Usage

As a basic rule of thumb, when calling `save()` on a `Baum\Node` instance,
if the `parent_id`, `lft` or `rgt` fields are left untouched, all nodes will
be created as _roots_. It's generally your job to move the newly created nodes
into their correct position.

This does not apply when using relations, as we'll see below.

### Making a root node

By default, all nodes are created as roots:

    $root = Category::create(['name' => 'The Root of All Evil']);

Alternatively, you may find yourself in the need of *converting* an existing node
into a *root node*:

    $node->makeRoot();

### Make a node a child of another node

    // using the $root node created in the previous example

    $dragons = Category::create(['name' => 'Here Be Dragons']);
    $dragons->makeChildOf($root);

### Make a node a sibling of another node

    // using the $dragons category from the previous example

    $monsters = new Category(['name' => 'Horrible Monsters']);
    $monsters->save();

    $monsters->makeSiblingOf($dragons);

Alternatively you may want to use `makePreviousSiblingOf(node)`
or `makeNextSiblingOf(node)` to gain control on the exact position of the sibling.

`makeSiblingOf` is an alias for `makeNextSiblingOf`.

### Moving nodes

TODO

## License

Baum is licensed under the terms of the [MIT License](http://opensource.org/licenses/MIT)
(See LICENSE file for details).

---

Coded by Estanislau Trepat.
