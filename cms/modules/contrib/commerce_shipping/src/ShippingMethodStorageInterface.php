<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the interface for shipping method storage.
 */
interface ShippingMethodStorageInterface extends ContentEntityStorageInterface {

  /**
   * Loads all eligible shipping methods for the given shipment.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The shipment.
   *
   * @return \Drupal\commerce_shipping\Entity\ShippingMethodInterface[]
   *   The shipping methods.
   */
  public function loadMultipleForShipment(ShipmentInterface $shipment);

}
