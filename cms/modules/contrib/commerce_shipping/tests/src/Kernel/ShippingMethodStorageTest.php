<?php

namespace Drupal\Tests\commerce_shipping\Kernel;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\Entity\ShippingMethod;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;

/**
 * Tests the shipping method storage.
 *
 * @group commerce
 */
class ShippingMethodStorageTest extends ShippingKernelTestBase {

  /**
   * A sample user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * A sample order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * A sample shipment.
   *
   * @var \Drupal\commerce_shipping\Entity\ShipmentInterface
   */
  protected $shipment;

  /**
   * A shipping method.
   *
   * @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface
   */
  protected $shippingMethod;

  /**
   * The shipping method storage.
   *
   * @var \Drupal\commerce_shipping\ShippingMethodStorageInterface
   */
  protected $storage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'address',
    'entity_reference_revisions',
    'profile',
    'state_machine',
    'commerce_order',
    'commerce_shipping',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('commerce_shipping_method');
    $this->installConfig('commerce_shipping');

    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);
    $this->user = $this->reloadEntity($user);

    $this->storage = $this->container->get('entity_type.manager')->getStorage('commerce_shipping_method');

    $this->order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => $this->user->getEmail(),
      'uid' => $this->user->id(),
      'store_id' => $this->store->id(),
    ]);
    $this->order->save();

    $this->shipment = Shipment::create([
      'type' => 'default',
      'items' => [
        new ShipmentItem([
          'order_item_id' => 10,
          'title' => 'T-shirt (red, large)',
          'quantity' => 1,
          'weight' => new Weight('10', 'kg'),
          'declared_value' => new Price('15.00', 'USD'),
        ]),
      ],
      'order_id' => $this->order->id(),
    ]);
    $this->shipment->save();
  }

  /**
   * Tests shipping method sorting by weight.
   */
  public function testSortingByWeight() {
    $shipping_method1 = ShippingMethod::create([
      'stores' => $this->store->id(),
      'name' => 'Example 1',
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'status' => TRUE,
      'weight' => 1,
    ]);
    $shipping_method1->save();
    $shipping_method2 = ShippingMethod::create([
      'stores' => $this->store->id(),
      'name' => 'Example 2',
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'status' => TRUE,
      'weight' => 2,
    ]);
    $shipping_method2->save();

    $shipping_methods = $this->storage->loadMultipleForShipment($this->shipment);
    $shipping_method = array_shift($shipping_methods);
    $this->assertEquals($shipping_method1->label(), $shipping_method->label());

    $shipping_method1->setWeight(99)->save();
    $shipping_methods = $this->storage->loadMultipleForShipment($this->shipment);
    $shipping_method = array_shift($shipping_methods);
    $this->assertEquals($shipping_method2->label(), $shipping_method->label());
  }

  /**
   * Tests shipping method filtering by store.
   */
  public function testFilteringByStore() {
    $second_store = $this->createStore('Default store 2', 'admin@example.com');

    $shipping_method1 = ShippingMethod::create([
      'stores' => $this->store->id(),
      'name' => 'Example 1',
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'status' => TRUE,
    ]);
    $shipping_method1->save();
    $shipping_method2 = ShippingMethod::create([
      'stores' => $second_store->id(),
      'name' => 'Example 2',
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'status' => TRUE,
    ]);
    $shipping_method2->save();

    $shipping_methods = $this->storage->loadMultipleForShipment($this->shipment);
    $this->assertCount(1, $shipping_methods);
    $this->assertEquals($shipping_method1->id(), reset($shipping_methods)->id());
  }

  /**
   * Tests shipping method filtering by status.
   */
  public function testFilteringByStatus() {
    $shipping_method1 = ShippingMethod::create([
      'stores' => $this->store->id(),
      'name' => 'Example 1',
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'status' => FALSE,
    ]);
    $shipping_method1->save();
    $shipping_method2 = ShippingMethod::create([
      'stores' => $this->store->id(),
      'name' => 'Example 2',
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'status' => TRUE,
    ]);
    $shipping_method2->save();

    $shipping_methods = $this->storage->loadMultipleForShipment($this->shipment);
    $this->assertCount(1, $shipping_methods);
    $this->assertEquals($shipping_method2->id(), reset($shipping_methods)->id());
  }

  /**
   * Tests shipping method filtering by conditions.
   */
  public function testFilteringByConditions() {
    $shipping_method1 = ShippingMethod::create([
      'stores' => $this->store->id(),
      'name' => 'Example 1',
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'conditions' => [
        [
          'target_plugin_id' => 'order_email',
          'target_plugin_configuration' => [
            'mail' => $this->user->getEmail(),
          ],
        ],
      ],
      'status' => TRUE,
    ]);
    $shipping_method1->save();
    // Shipping methods without conditions are always available.
    $shipping_method2 = ShippingMethod::create([
      'stores' => $this->store->id(),
      'name' => 'Example 2',
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'status' => TRUE,
    ]);
    $shipping_method2->save();

    // Both shipping methods should be available.
    $shipping_methods = $this->storage->loadMultipleForShipment($this->shipment);
    $this->assertEquals(2, count($shipping_methods));

    // Change the order email to disqualify the first shipping method.
    $this->order->setEmail('test@admin.com');
    $this->order->save();
    $this->shipment = $this->reloadEntity($this->shipment);
    $shipping_methods = $this->storage->loadMultipleForShipment($this->shipment);
    $this->assertCount(1, $shipping_methods);
    $this->assertEquals($shipping_method2->id(), reset($shipping_methods)->id());
  }

}
