<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Default implementation of the order shipment summary.
 *
 * Renders the shipping profile, then the information for each shipment.
 * Assumes that all shipments share the same shipping profile.
 */
class OrderShipmentSummary implements OrderShipmentSummaryInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new OrderShipmentSummary object.
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
  public function build(OrderInterface $order) {
    if (!$order->hasField('shipments') || $order->get('shipments')->isEmpty()) {
      return [];
    }
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface[] $shipments */
    $shipments = $order->get('shipments')->referencedEntities();
    $first_shipment = reset($shipments);
    $shipping_profile = $first_shipment->getShippingProfile();
    if (!$shipping_profile) {
      // Trying to generate a summary of incomplete shipments.
      return [];
    }
    $single_shipment = count($shipments) === 1;
    $profile_view_builder = $this->entityTypeManager->getViewBuilder('profile');
    $shipment_view_builder = $this->entityTypeManager->getViewBuilder('commerce_shipment');

    $summary = [];
    $summary['shipping_profile'] = $profile_view_builder->view($shipping_profile, 'default');
    foreach ($shipments as $index => $shipment) {
      $summary[$index] = [
        '#type' => $single_shipment ? 'container' : 'details',
        '#title' => $shipment->getTitle(),
        '#open' => TRUE,
      ];
      $summary[$index]['shipment'] = $shipment_view_builder->view($shipment, 'user');
      // The shipping profile is already shown above, the state is internal.
      $summary[$index]['shipment']['shipping_profile']['#access'] = FALSE;
      $summary[$index]['shipment']['state']['#access'] = FALSE;
    }

    return $summary;
  }

}
