<?php

namespace Drupal\Tests\commerce_order_number\Kernel\EventSubscriber;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order_number\OrderNumberFormatterInterface;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Tests OrderNumberSubscriber class.
 *
 * @coversDefaultClass \Drupal\commerce_order_number\EventSubscriber\OrderNumberSubscriber
 *
 * @group commerce_order_number
 */
class OrderNumberSubscriberTest extends CommerceKernelTestBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'path',
    'profile',
    'state_machine',
    'commerce_product',
    'commerce_order',
    'commerce_test',
    'commerce_order_number',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installConfig([
      'commerce_product',
      'commerce_order',
      'commerce_order_number',
    ]);
  }

  /**
   * Tests setting the order number on place transition.
   *
   * @covers ::setOrderNumber
   */
  public function testSetOrderNumber() {
    $config = $this->configFactory->getEditable('commerce_order_number.settings');
    $pattern = sprintf("#%s", OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_ORDER_NUMBER);
    $config->set('padding', 5)
      ->set('pattern', $pattern)
      ->set('force', FALSE)
      ->set('generator', 'infinite')
      ->save();

    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);

    /** @var \Drupal\commerce_order\Entity\Order $order1 */
    $order1 = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => 'text@example.com',
      'uid' => $user->id(),
      'ip_address' => '127.0.0.1',
      'store_id' => $this->store->id(),
    ]);
    $order1->save();

    $transition = $order1->getState()->getTransitions();
    $order1->getState()->applyTransition($transition['place']);
    $order1->save();
    $this->assertEquals('#00001', $order1->getOrderNumber());

    /** @var \Drupal\commerce_order\Entity\Order $order2 */
    $order2 = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => 'text@example.com',
      'uid' => $user->id(),
      'ip_address' => '127.0.0.1',
      'order_number' => '9999',
      'store_id' => $this->store->id(),
    ]);
    $order2->save();

    $transition = $order2->getState()->getTransitions();
    $order2->getState()->applyTransition($transition['place']);
    $order2->save();
    $this->assertEquals('9999', $order2->getOrderNumber(), 'Explicitly set order number should not get overridden, if force option is not set in configuration.');

    // Now, test force override option.
    $config->set('force', TRUE)->save();
    /** @var \Drupal\commerce_order\Entity\Order $order2 */
    $order3 = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => 'text@example.com',
      'uid' => $user->id(),
      'ip_address' => '127.0.0.1',
      'order_number' => '8888',
      'store_id' => $this->store->id(),
    ]);
    $order3->save();

    $transition = $order3->getState()->getTransitions();
    $order3->getState()->applyTransition($transition['place']);
    $order3->save();
    $this->assertEquals('#00002', $order3->getOrderNumber(), 'Explicitly set order number should be overridden, if force option is active in configuration.');
  }

}
