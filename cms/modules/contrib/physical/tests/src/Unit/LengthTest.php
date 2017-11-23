<?php

namespace Drupal\Tests\physical\Unit;

use Drupal\physical\Length;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the length class.
 *
 * @coversDefaultClass \Drupal\physical\Length
 * @group physical
 */
class LengthTest extends UnitTestCase {

  /**
   * The length.
   *
   * @var \Drupal\physical\Length
   */
  protected $length;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->length = new Length('3', 'm');
  }

  /**
   * ::covers __construct
   */
  public function testInvalidUnit() {
    $this->setExpectedException(\InvalidArgumentException::class);
    $length = new Length('1', 'kg');
  }

  /**
   * Tests unit conversion.
   *
   * ::covers convert
   */
  public function testConvert() {
    $this->assertEquals(new Length('3000', 'mm'), $this->length->convert('mm')->round());
    $this->assertEquals(new Length('300', 'cm'), $this->length->convert('cm')->round());
    $this->assertEquals(new Length('118.110', 'in'), $this->length->convert('in')->round(3));
    $this->assertEquals(new Length('9.843', 'ft'), $this->length->convert('ft')->round(3));
  }

}
