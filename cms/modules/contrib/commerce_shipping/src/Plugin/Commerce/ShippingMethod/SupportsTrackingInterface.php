<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_shipping\Entity\ShipmentInterface;

/**
 * Defines the interface for shipping methods which support tracking.
 */
interface SupportsTrackingInterface {

  /**
   * Gets the tracking url for the given shipment.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The shipment.
   *
   * @return \Drupal\core\Url|null
   *   The tracking URL, or NULL if not available yet.
   */
  public function getTrackingUrl(ShipmentInterface $shipment);

}
