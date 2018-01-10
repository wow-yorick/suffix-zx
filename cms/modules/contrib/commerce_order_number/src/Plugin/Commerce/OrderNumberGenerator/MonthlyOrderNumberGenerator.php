<?php

namespace Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator;

use Drupal\commerce_order_number\OrderNumber;

/**
 * Provides the yearly order number generator.
 *
 * @CommerceOrderNumberGenerator(
 *   id = "monthly",
 *   label = @Translation("Monthly"),
 *   description = @Translation("Reset every month, with an ID incremented at each order number generation"),
 * )
 */
class MonthlyOrderNumberGenerator extends OrderNumberGeneratorBase {

  /**
   * @inheritDoc
   */
  public function generate(OrderNumber $last_order_number = NULL) {
    $order_number = $last_order_number;
    $current_year = date('Y');
    $current_month = date('m');
    if (empty($order_number) || $current_year != $order_number->getYear() || $current_month != $order_number->getMonth()) {
      // Either no order number has been provided or the period does not match.
      $order_number = new OrderNumber(0, $current_year, $current_month);
    }
    $order_number->increment();
    return $order_number;
  }

}
