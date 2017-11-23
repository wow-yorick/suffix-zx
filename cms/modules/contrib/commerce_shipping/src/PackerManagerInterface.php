<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Runs the added packers one by one until one of them returns a result.
 *
 * @see \Drupal\commerce_shipment\ProposedShipment
 * @see \Drupal\commerce_shipment\Packer\PackerInterface
 */
interface PackerManagerInterface {

  /**
   * Adds a packer.
   *
   * @param \Drupal\commerce_shipping\Packer\PackerInterface $packer
   *   The packer.
   */
  public function addPacker(PackerInterface $packer);

  /**
   * Gets all added packers.
   *
   * @return \Drupal\commerce_shipping\Packer\PackerInterface[]
   *   The packers.
   */
  public function getPackers();

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

  /**
   * Packs the given order and populates the given shipments.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   * @param \Drupal\profile\Entity\ProfileInterface $shipping_profile
   *   The shipping profile.
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface[] $shipments
   *   The shipments to populate.
   *
   * @return array
   *   An array with the populated shipments as the first element, and the
   *   removed shipments as the second.
   */
  public function packToShipments(OrderInterface $order, ProfileInterface $shipping_profile, array $shipments);

}
