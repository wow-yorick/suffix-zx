<?php

namespace Drupal\commerce_shipping\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Creates one or more shipments for a given order.
 *
 * Allows sites to automatically split an order into multiple shipments
 * based on stock location, weight, dimensions, or some other criteria.
 *
 * Important:
 * A single order item can be added to multiple shipments only if all
 * shipments are going to share the same shipping profile.
 * This limitation is imposed by commerce_tax and CustomerProfileSubscriber
 * which need to select a shipping profile for each order item.
 */
interface PackerInterface {

  /**
   * Determines whether the packer applies to the given order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   * @param \Drupal\profile\Entity\ProfileInterface $shipping_profile
   *   The shipping profile.
   *
   * @return bool
   *   TRUE if the packer applies to the given order, FALSE otherwise.
   */
  public function applies(OrderInterface $order, ProfileInterface $shipping_profile);

  /**
   * Packs the given order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   * @param \Drupal\profile\Entity\ProfileInterface $shipping_profile
   *   The shipping profile.
   *
   * @return \Drupal\commerce_shipping\ProposedShipment[]
   *   The proposed shipments.
   */
  public function pack(OrderInterface $order, ProfileInterface $shipping_profile);

}
