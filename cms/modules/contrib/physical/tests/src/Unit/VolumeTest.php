<?php

namespace Drupal\Tests\physical\Unit;

use Drupal\physical\Volume;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the volume class.
 *
 * @coversDefaultClass \Drupal\physical\Volume
 * @group physical
 */
class VolumeTest extends UnitTestCase {

  /**
   * The volume.
   *
   * @var \Drupal\physical\Volume
   */
  protected $volume;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->volume = new Volume('4', 'm3');
  }

  /**
   * ::covers __construct
   */
  public function testInvalidUnit() {
    $this->setExpectedException(\InvalidArgumentException::class);
    $volume = new Volume('1', 'kg');
  }

  /**
   * Tests unit conversion.
   *
   * ::covers convert
   */
  public function testConvert() {
    $this->assertEquals(new Volume('4000000', 'ml'), $this->volume->convert('ml')->round());
    $this->assertEquals(new Volume('400000', 'cl'), $this->volume->convert('cl')->round());
    $this->assertEquals(new Volume('4000', 'l'), $this->volume->convert('l')->round());
    $this->assertEquals(new Volume('4000000000', 'mm3'), $this->volume->convert('mm3')->round());
    $this->assertEquals(new Volume('4000000', 'cm3'), $this->volume->convert('cm3')->round());
    $this->assertEquals(new Volume('244095', 'in3'), $this->volume->convert('in3')->round());
    $this->assertEquals(new Volume('141.259', 'ft3'), $this->volume->convert('ft3')->round(3));
    $this->assertEquals(new Volume('135256', 'fl oz'), $this->volume->convert('fl oz')->round());
    $this->assertEquals(new Volume('1056.69', 'gal'), $this->volume->convert('gal')->round(2));
  }

}
