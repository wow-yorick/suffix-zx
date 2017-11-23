<?php

namespace Drupal\Tests\commerce_shipping\Kernel;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\commerce_shipping\PackerManager;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping_test\Packer\TestPacker;
use Drupal\profile\Entity\Profile;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Tests the packer manager.
 *
 * @coversDefaultClass \Drupal\commerce_shipping\PackerManager
 * @group commerce_shipping
 */
class PackerManagerTest extends ShippingKernelTestBase {

  /**
   * The packer manager.
   *
   * @var \Drupal\commerce_shipping\PackerManager
   */
  protected $packerManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->packerManager = new PackerManager($entity_type_manager);
  }

  /**
   * ::covers addPacker
   * ::covers getPackers
   * ::covers pack.
   */
  public function testPack() {
    $order = $this->prophesize(OrderInterface::class)->reveal();
    $shipping_profile = $this->prophesize(ProfileInterface::class)->reveal();

    $first_proposed_shipment = $this->prophesize(ProposedShipment::class)->reveal();
    $second_proposed_shipment = $this->prophesize(ProposedShipment::class)->reveal();
    $third_proposed_shipment = $this->prophesize(ProposedShipment::class)->reveal();

    $first_packer = $this->prophesize(PackerInterface::class);
    $first_packer->applies($order, $shipping_profile)->willReturn(FALSE);
    $first_packer->pack($order, $shipping_profile)->willReturn([$first_proposed_shipment]);
    $first_packer = $first_packer->reveal();

    $second_packer = $this->prophesize(PackerInterface::class);
    $second_packer->applies($order, $shipping_profile)->willReturn(TRUE);
    $second_packer->pack($order, $shipping_profile)->willReturn([$second_proposed_shipment]);
    $second_packer = $second_packer->reveal();

    $third_packer = $this->prophesize(PackerInterface::class);
    $third_packer->applies($order, $shipping_profile)->willReturn(TRUE);
    $third_packer->pack($order, $shipping_profile)->willReturn([$third_proposed_shipment]);
    $third_packer = $third_packer->reveal();

    $this->packerManager->addPacker($first_packer);
    $this->packerManager->addPacker($second_packer);
    $this->packerManager->addPacker($third_packer);
    $expected_packers = [$first_packer, $second_packer, $third_packer];
    $packers = $this->packerManager->getPackers();
    $this->assertEquals($expected_packers, $packers, 'The manager has the expected packers');

    // Confirm that the first packer was skipped due to applies(), and the third one
    // was not reached.
    $result = $this->packerManager->pack($order, $shipping_profile);
    $this->assertEquals([$second_proposed_shipment], $result);
  }

  /**
   * ::covers packToShipments.
   */
  public function testPackToShipments() {
    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);
    $first_variation = ProductVariation::create([
      'type' => 'default',
      'sku' => 'test-product-01',
      'title' => 'Hat',
      'price' => new Price('10', 'USD'),
    ]);
    $first_variation->save();
    $second_variation = ProductVariation::create([
      'type' => 'default',
      'sku' => 'test-product-02',
      'title' => 'Mug',
      'price' => new Price('10', 'USD'),
    ]);
    $second_variation->save();

    $first_order_item = OrderItem::create([
      'type' => 'default',
      'quantity' => 2,
      'title' => $first_variation->getOrderItemTitle(),
      'purchased_entity' => $first_variation,
      'unit_price' => new Price('10', 'USD'),
    ]);
    $first_order_item->save();
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::create([
      'type' => 'default',
      'uid' => $user->id(),
      'store_id' => $this->store->id(),
      'order_items' => [$first_order_item],
    ]);
    $order->save();
    $shipping_profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'FR',
      ],
    ]);
    $shipping_profile->save();
    // Use the TestPacker that creates a shipment per order item.
    $this->packerManager->addPacker(new TestPacker());

    $shipments = [];
    list($shipments, $removed_shipments) = $this->packerManager->packToShipments($order, $shipping_profile, $shipments);
    $this->assertCount(1, $shipments);
    $shipment = $shipments[0];
    $this->assertEquals('Hat', $shipment->getItems()[0]->getTitle());
    $this->assertTrue($shipment->isNew());
    $this->assertTrue($shipment->getData('owned_by_packer'));
    $this->assertCount(0, $removed_shipments);
    $shipment->save();

    $second_order_item = OrderItem::create([
      'type' => 'default',
      'quantity' => 2,
      'title' => $second_variation->getOrderItemTitle(),
      'purchased_entity' => $second_variation,
      'unit_price' => new Price('10', 'USD'),
    ]);
    $second_order_item->save();
    $order->addItem($second_order_item);

    // The first shipment should be reused, and a second one created.
    $shipment_id = $shipment->id();
    $shipments = [$shipment];
    list($shipments, $removed_shipments) = $this->packerManager->packToShipments($order, $shipping_profile, $shipments);
    $this->assertCount(2, $shipments);
    $first_shipment = $shipments[0];
    $this->assertEquals($shipment_id, $first_shipment->id());
    $this->assertEquals('Hat', $first_shipment->getItems()[0]->getTitle());
    $this->assertFalse($first_shipment->isNew());
    $this->assertTrue($first_shipment->getData('owned_by_packer'));
    $second_shipment = $shipments[1];
    $this->assertEquals('Mug', $second_shipment->getItems()[0]->getTitle());
    $this->assertTrue($second_shipment->isNew());
    $this->assertTrue($second_shipment->getData('owned_by_packer'));
    $this->assertCount(0, $removed_shipments);

    // The second order item will now be packed as the first shipment.
    $order->removeItem($first_order_item);
    list($shipments, $removed_shipments) = $this->packerManager->packToShipments($order, $shipping_profile, $shipments);
    $this->assertCount(1, $shipments);
    $first_shipment = $shipments[0];
    $this->assertEquals($shipment_id, $first_shipment->id());
    $this->assertEquals('Mug', $first_shipment->getItems()[0]->getTitle());
    $this->assertFalse($first_shipment->isNew());
    $this->assertTrue($first_shipment->getData('owned_by_packer'));
    // The second shipment was never saved, so it's not in $removed_shipments.
    $this->assertCount(0, $removed_shipments);

    // No order items left.
    $order->removeItem($second_order_item);
    list($shipments, $removed_shipments) = $this->packerManager->packToShipments($order, $shipping_profile, $shipments);
    $this->assertCount(0, $shipments);
    $this->assertCount(1, $removed_shipments);
    $this->assertEquals($shipment_id, $removed_shipments[0]->id());
  }

}
