<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the interface for shipment storage.
 */
interface ShipmentStorageInterface extends ContentEntityStorageInterface {

  /**
   * Loads all shipments for the given order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return \Drupal\commerce_shipping\Entity\ShipmentInterface[]
   *   The shipments.
   */
  public function loadMultipleByOrder(OrderInterface $order);

}
