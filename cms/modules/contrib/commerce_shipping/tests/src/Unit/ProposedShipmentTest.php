<?php

namespace Drupal\Tests\commerce_shipping\Unit;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\ProposedShipment
 * @group commerce_shipping
 */
class ProposedShipmentTest extends UnitTestCase {

  /**
   * The proposed shipment.
   *
   * @var \Drupal\commerce_shipping\ProposedShipment
   */
  protected $proposedShipment;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $shipping_profile = $this->prophesize(ProfileInterface::class);
    $shipping_profile->id()->willReturn(11);

    $this->proposedShipment = new ProposedShipment([
      'type' => 'default',
      'order_id' => 10,
      'title' => 'Shipment from Narnia',
      'items' => [
        new ShipmentItem([
          'order_item_id' => 10,
          'title' => 'T-shirt (red, small)',
          'quantity' => 1,
          'weight' => new Weight('10', 'kg'),
          'declared_value' => new Price('10', 'USD'),
        ]),
      ],
      'shipping_profile' => $shipping_profile->reveal(),
      'package_type_id' => 'default',
      'custom_fields' => [
        'field_test' => 'value',
      ],
    ]);
  }

  /**
   * @covers ::getType
   */
  public function testGetType() {
    $this->assertEquals('default', $this->proposedShipment->getType());
  }

  /**
   * @covers ::getOrderId
   */
  public function testGetOrderId() {
    $this->assertEquals(10, $this->proposedShipment->getOrderId());
  }

  /**
   * @covers ::getTitle
   */
  public function testGetTitle() {
    $this->assertEquals('Shipment from Narnia', $this->proposedShipment->getTitle());
  }

  /**
   * @covers ::getItems
   */
  public function testGetItems() {
    $expected_items = [];
    $expected_items[] = new ShipmentItem([
      'order_item_id' => 10,
      'title' => 'T-shirt (red, small)',
      'quantity' => 1,
      'weight' => new Weight('10', 'kg'),
      'declared_value' => new Price('10', 'USD'),
    ]);
    $items = $this->proposedShipment->getItems();
    $this->assertArrayEquals($expected_items, $items);
  }

  /**
   * @covers ::getShippingProfile
   */
  public function testGetShippingProfile() {
    $this->assertEquals(11, $this->proposedShipment->getShippingProfile()->id());
  }

  /**
   * @covers ::getPackageTypeId
   */
  public function testGetPackageTypeId() {
    $this->assertEquals('default', $this->proposedShipment->getPackageTypeId());
  }

  /**
   * @covers ::getCustomFields
   */
  public function testGetCustomFields() {
    $this->assertEquals(['field_test' => 'value'], $this->proposedShipment->getCustomFields());
  }

  /**
   * @covers ::__construct
   */
  public function testMissingProperties() {
    $this->setExpectedException(\InvalidArgumentException::class, 'Missing required property "items".');
    $proposed_shipment = new ProposedShipment([
      'type' => 'default',
      'order_id' => 10,
      'title' => 'Test shipment',
      'package_type_id' => 'default',
    ]);
  }

  /**
   * @covers ::__construct
   */
  public function testInvalidItems() {
    $this->setExpectedException(\InvalidArgumentException::class, 'Each shipment item under the "items" property must be an instance of ShipmentItem.');
    $proposed_shipment = new ProposedShipment([
      'type' => 'default',
      'order_id' => 10,
      'title' => 'Test shipment',
      'items' => ['invalid'],
      'shipping_profile' => $this->prophesize(ProfileInterface::class)->reveal(),
      'package_type_id' => 'default',
    ]);
  }

}
