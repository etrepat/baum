<?php

namespace Baum\Tests;

use Mockery as m;

use Baum\Tests\Support\Models\Category;

class CustomEventsTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testMovementEventsFire()
    {
        $child1 = $this->categories('Child 1');
        $child3 = $this->categories('Child 3');

        $dispatcher = Category::getEventDispatcher();

        Category::setEventDispatcher($events = m::mock('Illuminate\Events\Dispatcher')->makePartial());

        $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($child1), $child1)->andReturn(true);

        $events->shouldReceive('dispatch')->once()->with('eloquent.moved: '.get_class($child1), $child1)->andReturn(true);

        $child1->moveToRightOf($child3);

        Category::unsetEventDispatcher();
        Category::setEventDispatcher($dispatcher);
    }

    public function testMovementHaltsWhenReturningFalseFromMoving()
    {
        $unchanged = $this->categories('Child 2');

        $dispatcher = Category::getEventDispatcher();

        Category::setEventDispatcher($events = m::mock('Illuminate\Events\Dispatcher')->makePartial());
        $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($unchanged), $unchanged)->andReturn(false);

        // Force "moving" to return false
        Category::moving(function ($node) {
            return false;
        });

        $unchanged->makeRoot();

        $unchanged->refresh();

        $this->assertEquals(1, $unchanged->getParentKey());
        $this->assertEquals(1, $unchanged->getLevel());
        $this->assertEquals(4, $unchanged->getLeft());
        $this->assertEquals(7, $unchanged->getRight());

        // Restore
        Category::getEventDispatcher()->forget('eloquent.moving: '.get_class($unchanged));

        Category::unsetEventDispatcher();
        Category::setEventDispatcher($dispatcher);
    }


    public function testMoving()
    {
        $dispatcher = Category::getEventDispatcher();

        Category::setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));

        $closure = function () {
        };

        $events->shouldReceive('listen')->once()->with('eloquent.moving: ' . Category::class, $closure);

        Category::moving($closure);

        Category::unsetEventDispatcher();

        Category::setEventDispatcher($dispatcher);
    }

    public function testMoved()
    {
        $dispatcher = Category::getEventDispatcher();

        Category::setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));

        $closure = function () {
        };

        $events->shouldReceive('listen')->once()->with('eloquent.moved: ' . Category::class, $closure);

        Category::moved($closure);

        Category::unsetEventDispatcher();

        Category::setEventDispatcher($dispatcher);
    }

    public function testGetObservableEventsIncludesMovingEvents()
    {
        $events = with(new Category)->getObservableEvents();

        $this->assertContains('moving', $events);
        $this->assertContains('moved', $events);
    }
}
