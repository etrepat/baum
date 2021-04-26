<?php

class BaumTestCase extends PHPUnit_Framework_TestCase {

  public function assertArraysAreEqual($expected, $actual, $message = '') {
    $ex = var_export($expected, true);
    $ac = var_export($actual, true);

    return $this->assertEquals($ex, $ac, $message);
  }

}
