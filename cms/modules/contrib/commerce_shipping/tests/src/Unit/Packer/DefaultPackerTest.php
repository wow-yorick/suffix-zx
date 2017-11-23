<?php

namespace Drupal\Tests\commerce_shipping\Unit\Packer;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_order\Entity\OrderTypeInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Packer\DefaultPacker;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\physical\Plugin\Field\FieldType\MeasurementItem;
use Drupal\physical\Weight;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\Packer\DefaultPacker
 * @group commerce_shipping
 */
class DefaultPackerTest extends UnitTestCase {

  /**
   * The default packer.
   *
   * @var \Drupal\commerce_shipping\Packer\DefaultPacker
   */
  protected $packer;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $order_type = $this->prophesize(OrderTypeInterface::class);
    $order_type->getThirdPartySetting('commerce_shipping', 'shipment_type')->willReturn('default');
    $order_type_storage = $this->prophesize(EntityStorageInterface::class);
    $order_type_storage->load('default')->willReturn($order_type->reveal());
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage('commerce_order_type')->willReturn($order_type_storage->reveal());

    $this->packer = new DefaultPacker($entity_type_manager->reveal());
  }

  /**
   * ::covers pack.
   */
  public function testPack() {
    $order_items = [];
    $first_order_item = $this->prophesize(OrderItemInterface::class);
    $first_order_item->id()->willReturn(2001);
    $first_order_item->getPurchasedEntity()->willReturn(NULL);
    $first_order_item->getQuantity()->willReturn(1);
    $order_items[] = $first_order_item->reveal(1);

    $weight_item = $this->prophesize(MeasurementItem::class);
    $weight_item->toMeasurement()->willReturn(new Weight('10', 'kg'));

    $weight_list = $this->prophesize(FieldItemListInterface::class);
    $weight_list->isEmpty()->willReturn(FALSE);
    $weight_list->first()->willReturn($weight_item->reveal());

    $purchased_entity = $this->prophesize(PurchasableEntityInterface::class);
    $purchased_entity->id()->willReturn(3001);
    $purchased_entity->getEntityTypeId()->willReturn('commerce_product_variation');
    $purchased_entity->hasField('weight')->willReturn(TRUE);
    $purchased_entity->get('weight')->willReturn($weight_list->reveal());
    $purchased_entity = $purchased_entity->reveal();
    $second_order_item = $this->prophesize(OrderItemInterface::class);
    $second_order_item->id()->willReturn(2002);
    $second_order_item->getTitle()->willReturn('T-shirt (red, small)');
    $second_order_item->getPurchasedEntity()->willReturn($purchased_entity);
    $second_order_item->getUnitPrice()->willReturn(new Price('15', 'USD'));
    $second_order_item->getQuantity()->willReturn(3);
    $order_items[] = $second_order_item->reveal();

    $order = $this->prophesize(OrderInterface::class);
    $order->bundle()->willReturn('default');
    $order->id()->willReturn(2);
    $order->getItems()->willReturn($order_items);
    $order = $order->reveal();
    $shipping_profile = $this->prophesize(ProfileInterface::class);
    $shipping_profile->id()->willReturn(3);
    $shipping_profile = $shipping_profile->reveal();

    $expected_proposed_shipment = new ProposedShipment([
      'type' => 'default',
      'order_id' => 2,
      'title' => 'Shipment #1',
      'items' => [
        new ShipmentItem([
          'order_item_id' => 2002,
          'title' => 'T-shirt (red, small)',
          'quantity' => 3,
          'weight' => new Weight('30', 'kg'),
          'declared_value' => new Price('45', 'USD'),
        ]),
      ],
      'shipping_profile' => $shipping_profile,
    ]);
    $result = $this->packer->pack($order, $shipping_profile);
    $this->assertEquals([$expected_proposed_shipment], $result);
  }

}

namespace Drupal\commerce_shipping\Packer;

if (!function_exists('t')) {

  /**
   * Mocks the t() function.
   *
   * @param string $string
   *   A string containing the English text to translate.
   * @param array $args
   *   (optional) An associative array of replacements to make after translation.
   *
   * @return string
   *   The translated string.
   */
  function t($string, array $args = []) {
    return strtr($string, $args);
  }

}
