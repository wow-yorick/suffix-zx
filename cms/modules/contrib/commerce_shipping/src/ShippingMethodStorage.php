<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce\CommerceContentEntityStorage;
use Drupal\commerce_shipping\Entity\ShipmentInterface;

/**
 * Defines the shipping method storage.
 */
class ShippingMethodStorage extends CommerceContentEntityStorage implements ShippingMethodStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadMultipleForShipment(ShipmentInterface $shipment) {
    $query = $this->getQuery();
    $query
      ->condition('stores', $shipment->getOrder()->getStore()->id())
      ->condition('status', TRUE);
    $result = $query->execute();
    $shipping_methods = $result ? $this->loadMultiple($result) : [];
    foreach ($shipping_methods as $shipping_method_id => $shipping_method) {
      if (!$shipping_method->applies($shipment)) {
        unset($shipping_methods[$shipping_method_id]);
      }
    }
    uasort($shipping_methods, [$this->entityType->getClass(), 'sort']);
    if (!empty($shipping_methods)) {
      // Allow modules to alter the list of available shipping methods via
      // hook_commerce_shipping_methods_alter(&$shipping_methods, $shipment).
      \Drupal::moduleHandler()->alter('commerce_shipping_methods', $shipping_methods, $shipment);
    }

    return $shipping_methods;
  }

}
