<?php

namespace Drupal\Tests\commerce_shipping\Unit\Plugin\Commerce\PackageType;

use Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageType;
use Drupal\physical\Length;
use Drupal\physical\Weight;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageType
 * @group commerce_shipping
 */
class PackageTypeTest extends UnitTestCase {

  /**
   * The test package type.
   *
   * @var \Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageType
   */
  protected $packageType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $plugin_definition = [
      'id' => 'test id',
      'remote_id' => 'test remote id',
      'label' => 'test label',
      'dimensions' => [
        'length' => '1',
        'width' => '2',
        'height' => '3',
        'unit' => 'mm',
      ],
      'weight' => [
        'number' => '4',
        'unit' => 'kg',
      ],
    ];
    $this->packageType = new PackageType([], 'test', $plugin_definition);
  }

  /**
   * @covers ::getId
   */
  public function testGetId() {
    $this->assertEquals('test id', $this->packageType->getId());
  }

  /**
   * @covers ::getRemoteId
   */
  public function testGetRemoteId() {
    $this->assertEquals('test remote id', $this->packageType->getRemoteId());
  }

  /**
   * @covers ::getLabel
   */
  public function testGetLabel() {
    $this->assertEquals('test label', $this->packageType->getLabel());
  }

  /**
   * @covers ::getLength
   * @covers ::getWidth
   * @covers ::getHeight
   */
  public function testGetDimensions() {
    $this->assertEquals(new Length('1', 'mm'), $this->packageType->getLength());
    $this->assertEquals(new Length('2', 'mm'), $this->packageType->getWidth());
    $this->assertEquals(new Length('3', 'mm'), $this->packageType->getHeight());
  }

  /**
   * @covers ::getWeight
   */
  public function testGetWeight() {
    $this->assertEquals(new Weight('4', 'kg'), $this->packageType->getWeight());
  }

}
