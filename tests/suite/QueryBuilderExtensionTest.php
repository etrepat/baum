<?php

use Mockery as m;
use Baum\Extensions\Query\Builder as QueryBuilder;

class QueryBuilderExtensionTest extends PHPUnit_Framework_TestCase {

  public function tearDown() {
    m::close();
  }

  protected function getBuilder() {
    $grammar    = new Illuminate\Database\Query\Grammars\Grammar;
    $processor  = m::mock('Illuminate\Database\Query\Processors\Processor');

    return new QueryBuilder(m::mock('Illuminate\Database\ConnectionInterface'), $grammar, $processor);
  }

  public function testReorderBy() {
    $builder = $this->getBuilder();

    $builder->select('*')->from('users')->orderBy('email')->orderBy('age', 'desc')->reOrderBy('full_name', 'asc');
    $this->assertEquals('select * from "users" order by "full_name" asc', $builder->toSql());
  }

  public function testAggregatesRemoveOrderBy() {
    $builder = $this->getBuilder();
    $builder->getConnection()->shouldReceive('select')->once()->with('select count(*) as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
    $builder->getProcessor()->shouldReceive('processSelect')->once()->andReturnUsing(function($builder, $results) { return $results; });
    $results = $builder->from('users')->orderBy('age', 'desc')->count();
    $this->assertEquals(1, $results);

    $builder = $this->getBuilder();
    $builder->getConnection()->shouldReceive('select')->once()->with('select count(*) as aggregate from "users" limit 1', array())->andReturn(array(array('aggregate' => 1)));
    $builder->getProcessor()->shouldReceive('processSelect')->once()->andReturnUsing(function($builder, $results) { return $results; });
    $results = $builder->from('users')->orderBy('age', 'desc')->exists();
    $this->assertTrue($results);

    $builder = $this->getBuilder();
    $builder->getConnection()->shouldReceive('select')->once()->with('select max("id") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
    $builder->getProcessor()->shouldReceive('processSelect')->once()->andReturnUsing(function($builder, $results) { return $results; });
    $results = $builder->from('users')->orderBy('age', 'desc')->max('id');
    $this->assertEquals(1, $results);

    $builder = $this->getBuilder();
    $builder->getConnection()->shouldReceive('select')->once()->with('select min("id") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
    $builder->getProcessor()->shouldReceive('processSelect')->once()->andReturnUsing(function($builder, $results) { return $results; });
    $results = $builder->from('users')->orderBy('age', 'desc')->min('id');
    $this->assertEquals(1, $results);

    $builder = $this->getBuilder();
    $builder->getConnection()->shouldReceive('select')->once()->with('select sum("id") as aggregate from "users"', array())->andReturn(array(array('aggregate' => 1)));
    $builder->getProcessor()->shouldReceive('processSelect')->once()->andReturnUsing(function($builder, $results) { return $results; });
    $results = $builder->from('users')->orderBy('age', 'desc')->sum('id');
    $this->assertEquals(1, $results);
  }

}
