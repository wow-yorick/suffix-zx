<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\profile\Entity\ProfileInterface;

class PackerManager implements PackerManagerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The packers.
   *
   * @var \Drupal\commerce_shipping\Packer\PackerInterface[]
   */
  protected $packers = [];

  /**
   * Constructs a new PackerManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function addPacker(PackerInterface $packer) {
    $this->packers[] = $packer;
  }

  /**
   * {@inheritdoc}
   */
  public function getPackers() {
    return $this->packers;
  }

  /**
   * {@inheritdoc}
   */
  public function pack(OrderInterface $order, ProfileInterface $shipping_profile) {
    $proposed_shipments = [];
    foreach ($this->packers as $packer) {
      if ($packer->applies($order, $shipping_profile)) {
        $proposed_shipments = $packer->pack($order, $shipping_profile);
        if (!is_null($proposed_shipments)) {
          break;
        }
      }
    }

    return $proposed_shipments;
  }

  /**
   * {@inheritdoc}
   */
  public function packToShipments(OrderInterface $order, ProfileInterface $shipping_profile, array $shipments) {
    $shipment_storage = $this->entityTypeManager->getStorage('commerce_shipment');
    $proposed_shipments = $this->pack($order, $shipping_profile);
    $populated_shipments = [];
    foreach ($proposed_shipments as $index => $proposed_shipment) {
      $shipment = NULL;
      // Take the first existing shipment of the matching type.
      foreach ($shipments as $existing_index => $existing_shipment) {
        if ($existing_shipment->bundle() == $proposed_shipment->getType()) {
          $shipment = $existing_shipment;
          unset($shipments[$existing_index]);
          break;
        }
      }

      if (!$shipment) {
        $shipment = $shipment_storage->create([
          'type' => $proposed_shipment->getType(),
        ]);
      }
      $shipment->populateFromProposedShipment($proposed_shipment);
      $shipment->setData('owned_by_packer', TRUE);
      $populated_shipments[$index] = $shipment;
    }
    $removed_shipments = array_filter($shipments, function ($shipment) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      return !$shipment->isNew();
    });

    return [$populated_shipments, (array) $removed_shipments];
  }

}
