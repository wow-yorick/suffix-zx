<?php

namespace Drupal\commerce_order_number;

use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Defines the order number service interface. The service is responsible for
 * handling the order number generation and will be called by our event
 * subscriber during order placement.
 *
 * This service will choose the right order number generator plugin according to
 * the site's configuration, as well as handling order number formatting.
 * Further it is responsible for guaranteeing thread safety, as well as that the
 * generated order number is unique.
 */
interface OrderNumberGenerationServiceInterface {

  /**
   * Generates and sets an order number for the given order entity. The function
   * is not responsible for saving the order number, this must be handled by the
   * calling function. The primary usage of this service class is to be called
   * by our event subscriber during order placement, where the order entity will
   * be finally saved anyway.
   *
   * The implementation is expected to pick the appropriate order number
   * generator plugin, taking care of only accepting an unique order number, as
   * well as formatting the order number according to the site's configuration.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return string|null
   *   The generated order number. Note, that this number is already set to the
   *   order. The return value is just for having easier access to the generated
   *   value. In case, that there was no order number set, NULL will be
   *   returned. This may only happen, if the given order as already an order
   *   number explicitly set and the site is configured to not override existing
   *   numbers.
   */
  public function generateAndSetOrderNumber(OrderInterface $order);

}
