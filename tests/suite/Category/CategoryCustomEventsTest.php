<?php

use Mockery as m;

class CategoryCustomEventsTest extends CategoryTestCase {

  public function tearDown() {
    m::close();
  }

  public function testMovementEventsFire() {
    $dispatcher = Category::getEventDispatcher();
    Category::setEventDispatcher($events = m::mock('Illuminate\Events\Dispatcher'));

    $child = $this->categories('Child 1');

    $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($child), $child)->andReturn(true);
    $events->shouldReceive('fire')->once()->with('eloquent.moved: '.get_class($child), $child)->andReturn(true);

    $child->moveToRightOf($this->categories('Child 3'));

    Category::unsetEventDispatcher();
    Category::setEventDispatcher($dispatcher);
  }

  public function testMovementHaltsWhenReturningFalseFromMoving() {
    $unchanged = $this->categories('Child 2');

    $dispatcher = Category::getEventDispatcher();

    Category::setEventDispatcher($events = m::mock('Illuminate\Events\Dispatcher[until]'));
    $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($unchanged), $unchanged)->andReturn(false);

    // Force "moving" to return false
    Category::moving(function($node) { return false; });

    $unchanged->makeRoot();

    $unchanged->reload();

    $this->assertEquals(1, $unchanged->getParentId());
    $this->assertEquals(1, $unchanged->getLevel());
    $this->assertEquals(4, $unchanged->getLeft());
    $this->assertEquals(7, $unchanged->getRight());

    // Restore
    Category::getEventDispatcher()->forget('eloquent.moving: '.get_class($unchanged));

    Category::unsetEventDispatcher();
    Category::setEventDispatcher($dispatcher);
  }

}
