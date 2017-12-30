<?php

namespace Drupal\Tests\commerce_order_number\Kernel\OrderNumberGenerator;

use Drupal\commerce_order_number\OrderNumber;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests InfiniteOrderNumberGenerator class.
 *
 * @coversDefaultClass \Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator\InfiniteOrderNumberGenerator
 *
 * @group commerce_order_number
 */
class InfiniteOrderNumberGeneratorTest extends EntityKernelTestBase {

  /**
   * The infinite order number generator.
   *
   * @var \Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator\InfiniteOrderNumberGenerator
   */
  protected $orderNumberGenerator;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_order_number',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\commerce_order_number\OrderNumberGeneratorManager $orderNumberGeneratorManager */
    $orderNumberGeneratorManager = $this->container->get('plugin.manager.commerce_order_number_generator');
    $this->orderNumberGenerator = $orderNumberGeneratorManager->createInstance('infinite');
  }

  /**
   * Tests the order number generation.
   *
   * @covers ::generate
   */
  public function testGenerate() {
    // First, test with empty parameter.
    $order_number = $this->orderNumberGenerator->generate();
    $this->assertEquals(1, $order_number->getIncrementNumber());

    // Now, test with existing order number as parameter.
    $order_number = new OrderNumber(5, '2011', '11');
    $order_number = $this->orderNumberGenerator->generate($order_number);
    $this->assertEquals(6, $order_number->getIncrementNumber());

    // And a last one, to see, if it keeps incrementing.
    $order_number = $this->orderNumberGenerator->generate($order_number);
    $this->assertEquals(7, $order_number->getIncrementNumber());
  }

}
