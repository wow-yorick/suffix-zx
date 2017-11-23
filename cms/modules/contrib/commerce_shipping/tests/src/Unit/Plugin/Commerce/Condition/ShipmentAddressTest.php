<?php

namespace Drupal\Tests\commerce_shipping\Unit\Plugin\Commerce\Condition;

use CommerceGuys\Addressing\Address;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\Plugin\Commerce\Condition\ShipmentAddress;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\Plugin\Commerce\Condition\ShipmentAddress
 * @group commerce
 */
class ShipmentAddressTest extends UnitTestCase {

  /**
   * ::covers evaluate.
   */
  public function testIncompleteShipment() {
    $condition = new ShipmentAddress([
      'zone' => [
        'territories' => [
          ['country_code' => 'US', 'administrative_area' => 'CA'],
        ],
      ],
    ], 'shipment_address', ['entity_type' => 'commerce_shipment']);
    $shipment = $this->prophesize(ShipmentInterface::class);
    $shipment->getEntityTypeId()->willReturn('commerce_shipment');
    $shipment->getShippingProfile()->willReturn(NULL);
    $shipment = $shipment->reveal();

    $this->assertFalse($condition->evaluate($shipment));
  }

  /**
   * ::covers evaluate.
   */
  public function testEvaluate() {
    $address_list = $this->prophesize(FieldItemListInterface::class);
    $address_list->first()->willReturn(new Address('US', 'SC'));
    $address_list = $address_list->reveal();
    $shipping_profile = $this->prophesize(ProfileInterface::class);
    $shipping_profile->get('address')->willReturn($address_list);
    $shipping_profile = $shipping_profile->reveal();
    $shipment = $this->prophesize(ShipmentInterface::class);
    $shipment->getEntityTypeId()->willReturn('commerce_shipment');
    $shipment->getShippingProfile()->willReturn($shipping_profile);
    $shipment = $shipment->reveal();

    $condition = new ShipmentAddress([
      'zone' => [
        'territories' => [
          ['country_code' => 'US', 'administrative_area' => 'CA'],
        ],
      ],
    ], 'shipment_address', ['entity_type' => 'commerce_shipment']);
    $this->assertFalse($condition->evaluate($shipment));

    $condition = new ShipmentAddress([
      'zone' => [
        'territories' => [
          ['country_code' => 'US', 'administrative_area' => 'SC'],
        ],
      ],
    ], 'shipment_address', ['entity_type' => 'commerce_shipment']);
    $this->assertTrue($condition->evaluate($shipment));
  }

}
