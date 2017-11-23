<?php

namespace Drupal\Tests\commerce_shipping\Unit;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\ShipmentItem
 * @group commerce_shipping
 */
class ShipmentItemTest extends UnitTestCase {

  /**
   * The shipment item.
   *
   * @var \Drupal\commerce_shipping\ShipmentItem
   */
  protected $shipmentItem;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->shipmentItem = new ShipmentItem([
      'order_item_id' => 10,
      'title' => 'T-shirt (red, small)',
      'quantity' => 1,
      'weight' => new Weight('10', 'kg'),
      'declared_value' => new Price('8', 'USD'),
      'tariff_code' => '7113.19.0000',
    ]);
  }

  /**
   * @covers ::getOrderItemId
   */
  public function testGetOrderItemId() {
    $this->assertEquals(10, $this->shipmentItem->getOrderItemId());
  }

  /**
   * @covers ::getTitle
   */
  public function testGetTitle() {
    $this->assertEquals('T-shirt (red, small)', $this->shipmentItem->getTitle());
  }

  /**
   * @covers ::getQuantity
   */
  public function testGetQuantity() {
    $this->assertEquals(1, $this->shipmentItem->getQuantity());
  }

  /**
   * @covers ::getWeight
   */
  public function testGetWeight() {
    $this->assertEquals(new Weight('10', 'kg'), $this->shipmentItem->getWeight());
  }

  /**
   * @covers ::getDeclaredValue
   */
  public function testGetDeclaredValue() {
    $this->assertEquals(new Price('8', 'USD'), $this->shipmentItem->getDeclaredValue());
  }

  /**
   * @covers ::getTariffCode
   */
  public function testGetTariffCode() {
    $this->assertEquals('7113.19.0000', $this->shipmentItem->getTariffCode());
  }

  /**
   * @covers ::__construct
   */
  public function testMissingProperties() {
    $this->setExpectedException(\InvalidArgumentException::class, 'Missing required property "declared_value".');
    $proposed_shipment = new ShipmentItem([
      'order_item_id' => 10,
      'title' => 'T-shirt (red, small)',
      'quantity' => 1,
      'weight' => new Weight('10', 'kg'),
    ]);
  }

}
