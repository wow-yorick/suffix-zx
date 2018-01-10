<?php

namespace Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator;

use Drupal\commerce_order_number\OrderNumber;

/**
 * Provides the infinite order number generator.
 *
 * @CommerceOrderNumberGenerator(
 *   id = "infinite",
 *   label = @Translation("Infinite"),
 *   description = @Translation("One single number, that is never reset and incremented at each order number generation"),
 * )
 */
class InfiniteOrderNumberGenerator extends OrderNumberGeneratorBase {

  /**
   * @inheritDoc
   */
  public function generate(OrderNumber $last_order_number = NULL) {
    $order_number = $last_order_number;
    if (empty($order_number)) {
      // No order number provided, create fresh one.
      $current_year = date('Y');
      $current_month = date('m');
      $order_number = new OrderNumber(0, $current_year, $current_month);
    }
    $order_number->increment();
    return $order_number;
  }

}
