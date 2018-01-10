<?php

namespace Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator;

use Drupal\commerce_order_number\OrderNumber;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for order number generators.
 */
interface OrderNumberGeneratorInterface extends PluginInspectionInterface {

  /**
   * Gets the order number generator label.
   *
   * @return string
   *   The order number generator label.
   */
  public function getLabel();

  /**
   * Gets the order number generator description.
   *
   * @return string
   *   The order number generator description.
   */
  public function getDescription();

  /**
   * Generates an order number value object, given the last known order number
   * as parameter.
   *
   * @param \Drupal\commerce_order_number\OrderNumber|null $last_order_number
   *   The last known order number value object. Can be left NULL. In this case,
   *   a fresh instantiated increment value will be returned.
   *
   * @return \Drupal\commerce_order_number\OrderNumber
   *   The generated order number value object. Normally, the returned object's
   *   increment number will be raised by one. Period based implementations may
   *   also reset this counter.
   */
  public function generate(OrderNumber $last_order_number = NULL);

}
