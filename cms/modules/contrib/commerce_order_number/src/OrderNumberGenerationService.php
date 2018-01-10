<?php

namespace Drupal\commerce_order_number;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Lock\LockBackendInterface;

/**
 * Default order number service implementation.
 */
class OrderNumberGenerationService implements OrderNumberGenerationServiceInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The key/value storage collection.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $keyValueStore;

  /**
   * The locking layer instance.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The order number formatter.
   *
   * @var \Drupal\commerce_order_number\OrderNumberFormatterInterface
   */
  protected $orderNumberFormatter;

  /**
   * The order number generator manager.
   *
   * @var \Drupal\commerce_order_number\OrderNumberGeneratorManager
   */
  protected $orderNumberGeneratorManager;

  /**
   * Constructs a new OrderNumberGenerationService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key value factory.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The locking layer instance.
   * @param \Drupal\commerce_order_number\OrderNumberFormatterInterface $order_number_formatter
   *   The order number formatter.
   * @param \Drupal\commerce_order_number\OrderNumberGeneratorManager $order_number_generator_manager
   *   The order number generator manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, KeyValueFactoryInterface $key_value_factory, LockBackendInterface $lock, OrderNumberFormatterInterface $order_number_formatter, OrderNumberGeneratorManager $order_number_generator_manager) {
    $this->configFactory = $config_factory;
    $this->keyValueStore = $key_value_factory->get('commerce_order_number');
    $this->lock = $lock;
    $this->orderNumberFormatter = $order_number_formatter;
    $this->orderNumberGeneratorManager = $order_number_generator_manager;
  }

  /**
   * @inheritDoc
   */
  public function generateAndSetOrderNumber(OrderInterface $order) {
    $config = $this->configFactory->get('commerce_order_number.settings');

    if (!empty($order->getOrderNumber()) && !$config->get('force')) {
      // The order already has an explicit order number set and the site is not
      // configured to force overrides.
      return NULL;
    }

    /** @var \Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator\OrderNumberGeneratorInterface $generator */
    $generator = $this->orderNumberGeneratorManager->createInstance($config->get('generator'));

    while (!$this->lock->acquire('commerce_order_number.generator')) {
      $this->lock->wait('commerce_order_number.generator');
    }

    $last_order_number = $this->keyValueStore->get('last_order_number', NULL);
    if (empty($last_order_number) || !($last_order_number instanceof OrderNumber)) {
      $last_order_number = NULL;
    }

    $order_number = $generator->generate($last_order_number);
    $order_number_formatted = $this->orderNumberFormatter->format($order_number);
    $order_number->setValue($order_number_formatted);

    // We check the value of the counter and keep incrementing until the value is unique.
    while (\Drupal::database()
      ->query('SELECT order_number FROM {commerce_order} WHERE order_number = :order_number', [':order_number' => $order_number_formatted])
      ->fetchField()) {
      $order_number->increment();
      $order_number = $generator->generate($last_order_number);
      $order_number_formatted = $this->orderNumberFormatter->format($order_number);
      $order_number->setValue($order_number_formatted);
    }

    $order->setOrderNumber($order_number_formatted);
    $this->keyValueStore->set('last_order_number', $order_number);
    $this->lock->release('commerce_order_number.generator');
    return $order_number_formatted;
  }

}
