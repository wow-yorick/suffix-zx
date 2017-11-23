<?php

namespace Drupal\Tests\commerce_shipping\Kernel;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Packer\DefaultPacker;
use Drupal\commerce_shipping\PackerManager;
use Drupal\commerce_shipping\ShipmentOrderProcessor;
use Drupal\commerce_shipping_test\Packer\TestPacker;
use Drupal\physical\Weight;
use Drupal\profile\Entity\Profile;

/**
 * Tests the shipment order processor.
 *
 * @coversDefaultClass \Drupal\commerce_shipping\ShipmentOrderProcessor
 * @group commerce_shipping
 */
class ShipmentOrderProcessorTest extends ShippingKernelTestBase {

  /**
   * The sample product variations.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface[]
   */
  protected $variations = [];

  /**
   * The sample order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * The order refresh processor.
   *
   * @var \Drupal\commerce_shipping\ShipmentOrderProcessor
   */
  protected $processor;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_checkout',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $entity_type_manager = $this->container->get('entity_type.manager');
    $packer_manager = new PackerManager($entity_type_manager);
    $packer_manager->addPacker(new TestPacker());
    $packer_manager->addPacker(new DefaultPacker($entity_type_manager));
    $this->processor = new ShipmentOrderProcessor($entity_type_manager, $packer_manager);

    $this->variations[] = ProductVariation::create([
      'type' => 'default',
      'sku' => 'test-product-01',
      'title' => 'Hat',
      'price' => new Price('10', 'USD'),
      'weight' => new Weight('0', 'g'),
    ]);
    $this->variations[] = ProductVariation::create([
      'type' => 'default',
      'sku' => 'test-product-02',
      'title' => 'Mug',
      'price' => new Price('10', 'USD'),
      'weight' => new Weight('0', 'g'),
    ]);
    $this->variations[0]->save();
    $this->variations[1]->save();

    $first_order_item = OrderItem::create([
      'type' => 'default',
      'quantity' => 2,
      'title' => $this->variations[0]->getOrderItemTitle(),
      'purchased_entity' => $this->variations[0],
      'unit_price' => new Price('10', 'USD'),
    ]);
    $first_order_item->save();
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::create([
      'type' => 'default',
      'uid' => $this->createUser(['mail' => $this->randomString() . '@example.com']),
      'store_id' => $this->store->id(),
      'order_items' => [$first_order_item],
    ]);
    $order->save();
    /** @var \Drupal\profile\Entity\ProfileInterface $shipping_profile */
    $shipping_profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'FR',
      ],
    ]);
    $shipping_profile->save();

    // Create the first shipment.
    list($shipments, $removed_shipments) = $packer_manager->packToShipments($order, $shipping_profile, []);
    $order->set('shipments', $shipments);
    $order->setRefreshState(Order::REFRESH_SKIP);
    $order->save();
    $this->order = $order;
    $this->shipping_profile = $shipping_profile;
  }

  /**
   * ::covers process
   * ::covers shouldRepack.
   */
  public function testProcess() {
    $this->assertCount(1, $this->order->get('shipments')->referencedEntities());

    // Repack on adding an order item.
    $second_order_item = OrderItem::create([
      'type' => 'default',
      'quantity' => 2,
      'title' => $this->variations[1]->getOrderItemTitle(),
      'purchased_entity' => $this->variations[1],
      'unit_price' => new Price('10', 'USD'),
    ]);
    $second_order_item->save();
    $this->order->addItem($second_order_item);
    $this->processor->process($this->order);
    $this->assertCount(2, $this->order->get('shipments')->referencedEntities());

    // No repack when the checkout page changed but the order items didn't.
    // The country change makes the DefaultPacker take over from TestPacker,
    // resulting in a single shipment.
    $this->shipping_profile->address->country_code = 'RS';
    $this->shipping_profile->save();
    $this->order->original = clone $this->order;
    $this->order->set('checkout_step', 'review');
    $this->processor->process($this->order);
    $this->assertCount(2, $this->order->get('shipments')->referencedEntities());

    // Repack when the checkout page changed but so did the order items.
    $this->order->original = clone $this->order;
    $this->order->original->set('checkout_step', 'order_information');
    $this->order->removeItem($second_order_item);
    $this->order->set('checkout_step', 'review');
    $this->processor->process($this->order);
    $this->assertCount(1, $this->order->get('shipments')->referencedEntities());
  }

}
