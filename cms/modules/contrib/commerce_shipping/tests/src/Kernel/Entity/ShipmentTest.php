<?php

namespace Drupal\Tests\commerce_shipping\Kernel\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\Entity\ShippingMethod;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\physical\Weight;
use Drupal\profile\Entity\Profile;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Tests the Shipment entity.
 *
 * @coversDefaultClass \Drupal\commerce_shipping\Entity\Shipment
 *
 * @group commerce_shipping
 */
class ShipmentTest extends CommerceKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'physical',
    'profile',
    'state_machine',
    'commerce_order',
    'commerce_product',
    'commerce_shipping',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_shipping_method');
    $this->installEntitySchema('commerce_shipment');
    $this->installConfig([
      'physical',
      'profile',
      'commerce_order',
      'commerce_shipping',
    ]);
  }

  /**
   * @covers ::getOrder
   * @covers ::getOrderId
   * @covers ::getPackageType
   * @covers ::setPackageType
   * @covers ::getShippingMethod
   * @covers ::getShippingMethodId
   * @covers ::setShippingMethod
   * @covers ::getShippingService
   * @covers ::setShippingService
   * @covers ::getShippingProfile
   * @covers ::setShippingProfile
   * @covers ::getTitle
   * @covers ::setTitle
   * @covers ::getItems
   * @covers ::setItems
   * @covers ::hasItems
   * @covers ::addItem
   * @covers ::removeItem
   * @covers ::getTotalDeclaredValue
   * @covers ::getWeight
   * @covers ::setWeight
   * @covers ::getAmount
   * @covers ::setAmount
   * @covers ::getTrackingCode
   * @covers ::setTrackingCode
   * @covers ::getState
   * @covers ::getData
   * @covers ::setData
   * @covers ::getCreatedTime
   * @covers ::setCreatedTime
   * @covers ::getShippedTime
   * @covers ::setShippedTime
   * @covers ::recalculateWeight
   */
  public function testShipment() {
    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => $user->getEmail(),
      'uid' => $user->id(),
      'store_id' => $this->store->id(),
    ]);
    $order->save();
    $order = $this->reloadEntity($order);

    /** @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface $shipping_method */
    $shipping_method = ShippingMethod::create([
      'name' => $this->randomString(),
      'status' => 1,
    ]);
    $shipping_method->save();
    $shipping_method = $this->reloadEntity($shipping_method);

    /** @var \Drupal\profile\Entity\ProfileInterface $profile */
    $profile = Profile::create([
      'type' => 'customer',
    ]);
    $profile->save();
    $profile = $this->reloadEntity($profile);

    $shipment = Shipment::create([
      'type' => 'default',
      'state' => 'ready',
      'order_id' => $order->id(),
    ]);

    $this->assertEquals($order, $shipment->getOrder());
    $this->assertEquals($order->id(), $shipment->getOrderId());

    $package_type_manager = \Drupal::service('plugin.manager.commerce_package_type');
    $package_type = $package_type_manager->createInstance('custom_box');
    $shipment->setPackageType($package_type);
    $this->assertEquals($package_type, $shipment->getPackageType());

    $shipment->setShippingMethod($shipping_method);
    $this->assertEquals($shipping_method, $shipment->getShippingMethod());
    $this->assertEquals($shipping_method->id(), $shipment->getShippingMethodId());

    $shipping_service = $this->randomString();
    $shipment->setShippingService($shipping_service);
    $this->assertEquals($shipping_service, $shipment->getShippingService());

    $shipment->setShippingProfile($profile);
    $this->assertEquals($profile, $shipment->getShippingProfile());

    $shipment->setTitle('Shipment #1');
    $this->assertEquals('Shipment #1', $shipment->getTitle());

    $items = [];
    $items[] = new ShipmentItem([
      'order_item_id' => 10,
      'title' => 'T-shirt (red, large)',
      'quantity' => 2,
      'weight' => new Weight('40', 'kg'),
      'declared_value' => new Price('30', 'USD'),
    ]);
    $items[] = new ShipmentItem([
      'order_item_id' => 10,
      'title' => 'T-shirt (blue, large)',
      'quantity' => 2,
      'weight' => new Weight('30', 'kg'),
      'declared_value' => new Price('30', 'USD'),
    ]);
    $shipment->addItem($items[0]);
    $shipment->addItem($items[1]);
    $this->assertTrue($shipment->hasItems());
    $this->assertEquals($items, $shipment->getItems());
    $shipment->removeItem($items[0]);
    $this->assertEquals([$items[1]], $shipment->getItems());
    $shipment->setItems($items);
    $this->assertEquals($items, $shipment->getItems());

    $this->assertEquals(new Price('60', 'USD'), $shipment->getTotalDeclaredValue());

    $calculated_weight = new Weight('70', 'kg');
    $this->assertEquals($calculated_weight, $shipment->getWeight()->convert('kg'));
    $new_weight = new Weight('4', 'kg');
    $shipment->setWeight($new_weight);
    $this->assertEquals($new_weight, $shipment->getWeight());

    $amount = new Price('10.00', 'USD');
    $shipment->setAmount($amount);
    $this->assertEquals($amount, $shipment->getAmount());

    $tracking_code = $this->randomString();
    $shipment->setTrackingCode($tracking_code);
    $this->assertEquals($tracking_code, $shipment->getTrackingCode());

    $this->assertEquals('ready', $shipment->getState()->value);

    $this->assertEquals('default', $shipment->getData('test', 'default'));
    $shipment->setData('test', 'value');
    $this->assertEquals('value', $shipment->getData('test', 'default'));

    $shipment->setCreatedTime(635879700);
    $this->assertEquals(635879700, $shipment->getCreatedTime());

    $shipment->setShippedTime(635879800);
    $this->assertEquals(635879800, $shipment->getShippedTime());
  }

  /**
   * @covers ::populateFromProposedShipment
   */
  public function testPopulatingFromProposedShipment() {
    /** @var \Drupal\profile\Entity\ProfileInterface $profile */
    $profile = Profile::create([
      'type' => 'customer',
    ]);
    $profile->save();
    $profile = $this->reloadEntity($profile);

    $proposed_shipment = new ProposedShipment([
      'type' => 'default',
      'order_id' => 10,
      'title' => 'Test title',
      'items' => [
        new ShipmentItem([
          'order_item_id' => 10,
          'title' => 'T-shirt (red, large)',
          'quantity' => 1,
          'weight' => new Weight('10', 'kg'),
          'declared_value' => new Price('15', 'USD'),
        ]),
      ],
      'shipping_profile' => $profile,
      'package_type_id' => 'custom_box',
      // State is not a custom field, but it simplifies this test.
      'custom_fields' => [
        'state' => 'ready',
        'no_field' => 'custom_value',
      ],
    ]);
    $shipment = Shipment::create([
      'type' => 'default',
    ]);
    $shipment->populateFromProposedShipment($proposed_shipment);

    $this->assertEquals($proposed_shipment->getOrderId(), $shipment->getOrderId());
    $this->assertEquals($profile, $shipment->getShippingProfile());
    $this->assertEquals($proposed_shipment->getTitle(), $shipment->getTitle());
    $this->assertEquals($proposed_shipment->getItems(), $shipment->getItems());
    $this->assertEquals($proposed_shipment->getPackageTypeId(), $shipment->getPackageType()->getId());
    $this->assertEquals('ready', $shipment->getState()->value);
    $this->assertEquals('custom_value', $shipment->getData('no_field'));
  }

  /**
   * @covers ::preSave
   */
  public function testDefaults() {
    /** @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface $shipping_method */
    $shipping_method = ShippingMethod::create([
      'name' => $this->randomString(),
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [],
      ],
      'status' => 1,
    ]);
    $shipping_method->save();

    // Saving a shipment with a shipping method but no package type should
    // populate the package type.
    $shipment = Shipment::create([
      'type' => 'default',
      'order_id' => 10,
      'shipping_method' => $shipping_method,
      'items' => [
        new ShipmentItem([
          'order_item_id' => 10,
          'title' => 'T-shirt (red, large)',
          'quantity' => 1,
          'weight' => new Weight('10', 'kg'),
          'declared_value' => new Price('15', 'USD'),
        ]),
      ],
    ]);
    $shipment->save();
    $this->assertEquals('custom_box', $shipment->getPackageType()->getId());
  }

  /**
   * @covers ::preSave
   */
  public function testEmptyValidation() {
    $shipment = Shipment::create([
      'type' => 'default',
      'order_id' => 10,
    ]);
    $this->setExpectedException(EntityStorageException::class, 'Required shipment field "items" is empty.');
    $shipment->save();
  }

}
