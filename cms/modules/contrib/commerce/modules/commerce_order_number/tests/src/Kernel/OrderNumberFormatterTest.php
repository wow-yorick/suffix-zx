<?php

namespace Drupal\Tests\commerce_order_number\Kernel;

use Drupal\commerce_order_number\OrderNumber;
use Drupal\commerce_order_number\OrderNumberFormatterInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests OrderNumberFormatter class.
 *
 * @coversDefaultClass \Drupal\commerce_order_number\OrderNumberFormatter
 *
 * @group commerce_order_number
 */
class OrderNumberFormatterTest extends EntityKernelTestBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The yearly order number generator.
   *
   * @var \Drupal\commerce_order_number\OrderNumberFormatterInterface
   */
  protected $orderNumberFormatter;

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

    $this->configFactory = $this->container->get('config.factory');
    $this->orderNumberFormatter = $this->container->get('commerce_order_number.order_number_formatter');
  }

  /**
   * Tests the order number formatting function.
   *
   * @covers ::format
   */
  public function testFormat() {
    $config = $this->configFactory->getEditable('commerce_order_number.settings');
    $order_number = new OrderNumber(123, '2017', '01');

    // First, try the minimal configuration, without number padding and plain order number pattern.
    $config->set('padding', 0)
      ->set('pattern', OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_ORDER_NUMBER)
      ->save();
    $formatted_order_number = $this->orderNumberFormatter->format($order_number);
    $this->assertEquals('123', $formatted_order_number);

    // Now, test number padding.
    $config->set('padding', 6)->save();
    $formatted_order_number = $this->orderNumberFormatter->format($order_number);
    $this->assertSame('000123', $formatted_order_number);

    // Next, test pattern with prefix and suffix.
    $pattern = sprintf("#%so", OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_ORDER_NUMBER);
    $config->set('pattern', $pattern)->save();
    $formatted_order_number = $this->orderNumberFormatter->format($order_number);
    $this->assertEquals('#000123o', $formatted_order_number);

    // Test year and month patterns as well.
    $pattern = sprintf("#%s%s%s", OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_YEAR, OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_MONTH, OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_ORDER_NUMBER);
    $config->set('pattern', $pattern)->save();
    $formatted_order_number = $this->orderNumberFormatter->format($order_number);
    $this->assertEquals('#201701000123', $formatted_order_number);
  }

}
