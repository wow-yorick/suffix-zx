<?php

namespace Drupal\Tests\physical\Unit;

use Drupal\physical\Area;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the area class.
 *
 * @coversDefaultClass \Drupal\physical\Area
 * @group physical
 */
class AreaTest extends UnitTestCase {

  /**
   * The area.
   *
   * @var \Drupal\physical\Area
   */
  protected $area;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->area = new Area('4', 'm2');
  }

  /**
   * ::covers __construct
   */
  public function testInvalidUnit() {
    $this->setExpectedException(\InvalidArgumentException::class);
    $area = new Area('2', 'cm3');
  }

  /**
   * Tests unit conversion.
   *
   * ::covers convert
   */
  public function testConvert() {
    $this->assertEquals(new Area('4000000', 'mm2'), $this->area->convert('mm2')->round());
    $this->assertEquals(new Area('40000', 'cm2'), $this->area->convert('cm2')->round());
    $this->assertEquals(new Area('6200.01', 'in2'), $this->area->convert('in2')->round(2));
    $this->assertEquals(new Area('43.05564', 'ft2'), $this->area->convert('ft2')->round(5));
  }

}
