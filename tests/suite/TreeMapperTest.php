<?php

namespace Baum\Tests;

use Illuminate\Database\Capsule\Manager as DB;

use Baum\Tests\Support\Migrators\CategoryMigrator;
use Baum\Tests\Support\Models\Category;
use Baum\Tests\Support\Seeders\CategorySeeder;

class TreeMapperTest extends TestCase
{
    public function setUp(): void
    {
        with(new CategoryMigrator)->up();
    }

    public function testBuildTree()
    {
        $tree = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            [
                'id' => 3, 'name' => 'C', 'children' => [
                    [
                        'id' => 4, 'name' => 'C.1', 'children' => [
                            ['id' => 5, 'name' => 'C.1.1'],
                            ['id' => 6, 'name' => 'C.1.2']
                        ]
                    ],
                    ['id' => 7, 'name' => 'C.2'],
                    ['id' => 8, 'name' => 'C.3']
                ]
            ],
            ['id' => 9, 'name' => 'D']
        ];

        $this->assertTrue(Category::buildTree($tree));
        $this->assertTrue(Category::isValidNestedSet());

        $hierarchy = Category::all()->toHierarchy()->toArray();
        $this->assertEquals($tree, array_ints_keys(hmap($hierarchy, ['id', 'name'])));
    }

    public function testBuildTreePrunesAndInserts()
    {
        $tree = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            [
                'id' => 3, 'name' => 'C', 'children' => [
                    [
                        'id' => 4, 'name' => 'C.1', 'children' => [
                            ['id' => 5, 'name' => 'C.1.1'],
                            ['id' => 6, 'name' => 'C.1.2']
                        ]
                    ],
                    ['id' => 7, 'name' => 'C.2'],
                    ['id' => 8, 'name' => 'C.3']
                ]
            ],
            ['id' => 9, 'name' => 'D']
        ];

        $this->assertTrue(Category::buildTree($tree));
        $this->assertTrue(Category::isValidNestedSet());

        // Postgres fix
        if (DB::connection()->getDriverName() === 'pgsql') {
            $tablePrefix = DB::connection()->getTablePrefix();

            $sequenceName = $tablePrefix . 'categories_id_seq';

            DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 10');
        }

        $updated = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            [
                'id' => 3, 'name' => 'C', 'children' => [
                    [
                        'id' => 4, 'name' => 'C.1', 'children' => [
                            ['id' => 5, 'name' => 'C.1.1'],
                            ['id' => 6, 'name' => 'C.1.2']
                        ]
                    ],
                    [
                        'id' => 7, 'name' => 'C.2', 'children' => [
                            ['name' => 'C.2.1'],
                            ['name' => 'C.2.2']
                        ]
                    ]
                ]
            ],
            ['id' => 9, 'name' => 'D']
        ];
        $this->assertTrue(Category::buildTree($updated));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            [
                'id' => 3, 'name' => 'C', 'children' => [
                    [
                        'id' => 4, 'name' => 'C.1', 'children' => [
                            ['id' => 5, 'name' => 'C.1.1'],
                            ['id' => 6, 'name' => 'C.1.2']
                        ]
                    ],
                    [
                        'id' => 7, 'name' => 'C.2', 'children' => [
                            ['id' => 10, 'name' => 'C.2.1'],
                            ['id' => 11, 'name' => 'C.2.2']
                        ]
                    ]
                ]
            ],
            ['id' => 9, 'name' => 'D']
        ];

        $hierarchy = Category::all()->toHierarchy()->toArray();
        $this->assertEquals($expected, array_ints_keys(hmap($hierarchy, ['id', 'name'])));
    }

    public function testMakeTree()
    {
        with(new CategorySeeder)->run();

        $parent = Category::find(3);

        $subtree = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['name' => 'Child 2.2'],
            [
                'name' => 'Child 2.3', 'children' => [
                    [
                        'name' => 'Child 2.3.1', 'children' => [
                            ['name' => 'Child 2.3.1.1'],
                            ['name' => 'Child 2.3.1.1']
                        ]
                    ],
                    ['name' => 'Child 2.3.2'],
                    ['name' => 'Child 2.3.3']
                ]
            ],
            ['name' => 'Child 2.4']
        ];

        $this->assertTrue($parent->makeTree($subtree));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['id' => 7, 'name' => 'Child 2.2'],
            [
                'id' => 8, 'name' => 'Child 2.3', 'children' => [
                    [
                        'id' => 9, 'name' => 'Child 2.3.1', 'children' => [
                            ['id' => 10, 'name' => 'Child 2.3.1.1'],
                            ['id' => 11, 'name' => 'Child 2.3.1.1']
                        ]
                    ],
                    ['id' => 12, 'name' => 'Child 2.3.2'],
                    ['id' => 13, 'name' => 'Child 2.3.3']
                ]
            ],
            ['id' => 14, 'name' => 'Child 2.4']
        ];

        $hierarchy = $parent->refresh()->getDescendants()->toHierarchy()->toArray();
        $this->assertEquals($expected, array_ints_keys(hmap($hierarchy, ['id', 'name'])));
    }

    public function testMakeTreePrunesAndInserts()
    {
        with(new CategorySeeder)->run();

        $parent = Category::find(3);

        $subtree = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['name' => 'Child 2.2'],
            [
                'name' => 'Child 2.3', 'children' => [
                    [
                        'name' => 'Child 2.3.1', 'children' => [
                            ['name' => 'Child 2.3.1.1'],
                            ['name' => 'Child 2.3.1.1']
                        ]
                    ],
                    ['name' => 'Child 2.3.2'],
                    ['name' => 'Child 2.3.3']
                ]
            ],
            ['name' => 'Child 2.4']
        ];

        $this->assertTrue($parent->makeTree($subtree));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['id' => 7, 'name' => 'Child 2.2'],
            [
                'id' => 8, 'name' => 'Child 2.3', 'children' => [
                    [
                        'id' => 9, 'name' => 'Child 2.3.1', 'children' => [
                            ['id' => 10, 'name' => 'Child 2.3.1.1'],
                            ['id' => 11, 'name' => 'Child 2.3.1.1']
                        ]
                    ],
                    ['id' => 12, 'name' => 'Child 2.3.2'],
                    ['id' => 13, 'name' => 'Child 2.3.3']
                ]
            ],
            ['id' => 14, 'name' => 'Child 2.4']
        ];

        $hierarchy = $parent->refresh()->getDescendants()->toHierarchy()->toArray();
        $this->assertEquals($expected, array_ints_keys(hmap($hierarchy, ['id', 'name'])));

        $modified = [
            ['id' => 7, 'name' => 'Child 2.2'],
            ['id' => 8, 'name' => 'Child 2.3'],
            ['id' => 14, 'name' => 'Child 2.4'],
            [
                'name' => 'Child 2.5', 'children' => [
                    [
                        'name' => 'Child 2.5.1', 'children' => [
                            ['name' => 'Child 2.5.1.1'],
                            ['name' => 'Child 2.5.1.1']
                        ]
                    ],
                    ['name' => 'Child 2.5.2'],
                    ['name' => 'Child 2.5.3']
                ]
            ]
        ];

        $this->assertTrue($parent->makeTree($modified));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 7 , 'name' => 'Child 2.2'],
            ['id' => 8 , 'name' => 'Child 2.3'],
            ['id' => 14, 'name' => 'Child 2.4'],
            [
                'id' => 15, 'name' => 'Child 2.5', 'children' => [
                    [
                        'id' => 16, 'name' => 'Child 2.5.1', 'children' => [
                            ['id' => 17, 'name' => 'Child 2.5.1.1'],
                            ['id' => 18, 'name' => 'Child 2.5.1.1']
                        ]
                    ],
                    ['id' => 19, 'name' => 'Child 2.5.2'],
                    ['id' => 20, 'name' => 'Child 2.5.3']
                ]
            ]
        ];

        $hierarchy = $parent->refresh()->getDescendants()->toHierarchy()->toArray();
        $this->assertEquals($expected, array_ints_keys(hmap($hierarchy, ['id', 'name'])));
    }
}
