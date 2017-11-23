<?php

namespace Drupal\Tests\commerce_shipping\Kernel;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;

/**
 * Tests the interaction between order and shipping workflows.
 *
 * @group commerce_shipping
 */
class OrderWorkflowTest extends ShippingKernelTestBase {

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);

    OrderItemType::create([
      'id' => 'test',
      'label' => 'Test',
      'orderType' => 'default',
    ])->save();
    $order_item = OrderItem::create([
      'type' => 'test',
      'quantity' => 1,
      'unit_price' => new Price('12.00', 'USD'),
    ]);
    $order_item->save();
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => $user->getEmail(),
      'uid' => $user->id(),
      'store_id' => $this->store->id(),
      'order_items' => [$order_item],
    ]);
    $order->save();
    $this->order = $this->reloadEntity($order);

    $shipment = Shipment::create([
      'type' => 'default',
      'order_id' => $this->order->id(),
      'items' => [
        new ShipmentItem([
          'order_item_id' => 10,
          'title' => 'T-shirt (red, large)',
          'quantity' => 2,
          'weight' => new Weight('40', 'kg'),
          'declared_value' => new Price('30', 'USD'),
        ]),
      ],
      'amount' => new Price('5', 'USD'),
      'state' => 'draft',
    ]);
    $shipment->save();
    $this->shipment = $this->reloadEntity($shipment);

    $this->order->set('shipments', [$shipment]);
    $this->order->save();
  }

  /**
   * Tests the order cancellation.
   */
  public function testOrderCancellation() {
    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['cancel']);
    $this->order->save();

    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('canceled', $shipment->getState()->value, 'The shipment has been correctly canceled.');
  }

  /**
   * Tests the order fulfillment.
   */
  public function testOrderFulfillment() {
    $order_type = OrderType::load($this->order->bundle());
    $order_type->setWorkflowId('order_fulfillment');
    $order_type->save();

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['place']);
    $this->order->save();

    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('ready', $shipment->getState()->value, 'The shipment has been correctly finalized.');

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['fulfill']);
    $this->order->save();
    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('shipped', $shipment->getState()->value);
  }

  /**
   * Tests the order validation.
   */
  public function testOrderValidation() {
    $order_type = OrderType::load($this->order->bundle());
    $order_type->setWorkflowId('order_fulfillment_validation');
    $order_type->save();

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['place']);
    $this->order->save();
    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('draft', $shipment->getState()->value);

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['validate']);
    $this->order->save();
    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('ready', $shipment->getState()->value);

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['fulfill']);
    $this->order->save();
    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('shipped', $shipment->getState()->value);
  }

}
