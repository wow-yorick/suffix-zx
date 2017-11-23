<?php

namespace Drupal\Tests\physical\Unit;

use Drupal\physical\Length;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the measurement base class.
 *
 * @coversDefaultClass \Drupal\physical\Measurement
 * @group physical
 */
class MeasurementTest extends UnitTestCase {

  /**
   * The measurement.
   *
   * @var \Drupal\physical\Measurement
   */
  protected $measurement;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->measurement = new Length('10', 'm');
  }

  /**
   * ::covers __construct
   */
  public function testInvalidNumber() {
    $this->setExpectedException(\InvalidArgumentException::class);
    $measurement = new Length('INVALID', 'm');
  }

  /**
   * Tests the methods for getting the number and unit in various formats.
   *
   * ::covers getNumber
   * ::covers getUnit
   * ::covers __toString
   * ::covers toArray
   */
  public function testGetters() {
    $this->assertEquals('10', $this->measurement->getNumber());
    $this->assertEquals('m', $this->measurement->getUnit());
    $this->assertEquals('10 m', $this->measurement->__toString());
    $this->assertEquals(['number' => '10', 'unit' => 'm'], $this->measurement->toArray());
  }

  /**
   * Tests the arithmetic methods.
   *
   * ::covers add
   * ::covers subtract
   * ::covers multiply
   * ::covers divide
   */
  public function testArithmetic() {
    $result = $this->measurement->add(new Length('5', 'm'));
    $this->assertEquals(new Length('15', 'm'), $result);

    $result = $this->measurement->subtract(new Length('5', 'm'));
    $this->assertEquals(new Length('5', 'm'), $result);

    $result = $this->measurement->multiply('5');
    $this->assertEquals(new Length('50', 'm'), $result);

    $result = $this->measurement->divide('10');
    $this->assertEquals(new Length('1', 'm'), $result);
  }

  /**
   * Tests the comparison methods.
   *
   * ::covers isZero
   * ::covers equals
   * ::covers greaterThan
   * ::covers greaterThanOrEqual
   * ::covers lessThan
   * ::covers lessThanOrEqual
   * ::covers compareTo
   */
  public function testComparison() {
    $this->assertFalse($this->measurement->isZero());
    $zero_measurement = new Length('0', 'm');
    $this->assertTrue($zero_measurement->isZero());

    $this->assertTrue($this->measurement->equals(new Length('10', 'm')));
    $this->assertFalse($this->measurement->equals(new Length('15', 'm')));

    $this->assertTrue($this->measurement->greaterThan(new Length('5', 'm')));
    $this->assertFalse($this->measurement->greaterThan(new Length('10', 'm')));
    $this->assertFalse($this->measurement->greaterThan(new Length('15', 'm')));

    $this->assertTrue($this->measurement->greaterThanOrEqual(new Length('5', 'm')));
    $this->assertTrue($this->measurement->greaterThanOrEqual(new Length('10', 'm')));
    $this->assertFalse($this->measurement->greaterThanOrEqual(new Length('15', 'm')));

    $this->assertTrue($this->measurement->lessThan(new Length('15', 'm')));
    $this->assertFalse($this->measurement->lessThan(new Length('10', 'm')));
    $this->assertFalse($this->measurement->lessThan(new Length('5', 'm')));

    $this->assertTrue($this->measurement->lessThanOrEqual(new Length('15', 'm')));
    $this->assertTrue($this->measurement->lessThanOrEqual(new Length('10', 'm')));
    $this->assertFalse($this->measurement->lessThanOrEqual(new Length('5', 'm')));
  }

}
