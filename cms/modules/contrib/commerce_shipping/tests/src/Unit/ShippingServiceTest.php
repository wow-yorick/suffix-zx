<?php

namespace Drupal\Tests\commerce_shipping\Unit;

use Drupal\commerce_shipping\ShippingService;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\ShippingService
 * @group commerce_shipping
 */
class ShippingServiceTest extends UnitTestCase {

  /**
   * The shipping service.
   *
   * @var \Drupal\commerce_shipping\ShippingService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->service = new ShippingService('test', 'Test');
  }

  /**
   * @covers ::getId
   */
  public function testGetId() {
    $this->assertEquals('test', $this->service->getId());
  }

  /**
   * @covers ::getLabel
   */
  public function testGetLabel() {
    $this->assertEquals('Test', $this->service->getLabel());
  }

}
