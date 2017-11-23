<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce\CommerceContentEntityStorage;
use Drupal\commerce_order\Entity\OrderInterface;

class ShipmentStorage extends CommerceContentEntityStorage implements ShipmentStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadMultipleByOrder(OrderInterface $order) {
    return $this->loadByProperties(['order_id' => $order->id()]);
  }

}
