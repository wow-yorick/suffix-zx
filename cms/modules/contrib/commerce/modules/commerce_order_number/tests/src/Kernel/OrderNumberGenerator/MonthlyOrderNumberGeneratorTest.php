<?php

namespace Drupal\Tests\commerce_order_number\Kernel\OrderNumberGenerator;

use Drupal\commerce_order_number\OrderNumber;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests MonthlyOrderNumberGenerator class.
 *
 * @coversDefaultClass \Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator\MonthlyOrderNumberGenerator
 *
 * @group commerce_order_number
 */
class MonthlyOrderNumberGeneratorTest extends EntityKernelTestBase {

  /**
   * The monthly order number generator.
   *
   * @var \Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator\MonthlyOrderNumberGenerator
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
    $this->orderNumberGenerator = $orderNumberGeneratorManager->createInstance('monthly');
  }

  /**
   * Tests the order number generation.
   *
   * @covers ::generate
   */
  public function testGenerate() {
    $current_year = date('Y');
    $current_month = date('m');
    $different_month = (int) date('n');
    if ($different_month > 1) {
      $different_month--;
    }
    else {
      $different_month++;
    }
    $different_month = str_pad($different_month, 2, '0', STR_PAD_LEFT);

    // First, test with empty parameter.
    $order_number = $this->orderNumberGenerator->generate();
    $this->assertEquals(1, $order_number->getIncrementNumber());

    // Now, test with existing order number from a different month as parameter.
    $order_number = new OrderNumber(5, $current_year, $different_month);
    $order_number = $this->orderNumberGenerator->generate($order_number);
    $this->assertEquals(1, $order_number->getIncrementNumber());

    // Finally, test with existing order number from current year as parameter.
    $order_number = new OrderNumber(5, $current_year, $current_month);
    $order_number = $this->orderNumberGenerator->generate($order_number);
    $this->assertEquals(6, $order_number->getIncrementNumber());

    // And a last one, to see, if it keeps incrementing.
    $order_number = $this->orderNumberGenerator->generate($order_number);
    $this->assertEquals(7, $order_number->getIncrementNumber());
  }

}
