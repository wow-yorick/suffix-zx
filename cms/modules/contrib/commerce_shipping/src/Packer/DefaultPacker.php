<?php

namespace Drupal\commerce_shipping\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Creates a single shipment per order.
 */
class DefaultPacker implements PackerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DefaultPacker object.
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
  public function applies(OrderInterface $order, ProfileInterface $shipping_profile) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function pack(OrderInterface $order, ProfileInterface $shipping_profile) {
    $items = [];
    foreach ($order->getItems() as $order_item) {
      $purchased_entity = $order_item->getPurchasedEntity();
      // Ship only shippable purchasable entity types.
      if (!$purchased_entity || !$purchased_entity->hasField('weight')) {
        continue;
      }
      // The weight will be empty if the shippable trait was added but the
      // existing entities were not updated.
      if ($purchased_entity->get('weight')->isEmpty()) {
        $purchased_entity->set('weight', new Weight(0, WeightUnit::GRAM));
      }

      $quantity = $order_item->getQuantity();
      /** @var \Drupal\physical\Weight $weight */
      $weight = $purchased_entity->get('weight')->first()->toMeasurement();
      $items[] = new ShipmentItem([
        'order_item_id' => $order_item->id(),
        'title' => $order_item->getTitle(),
        'quantity' => $quantity,
        'weight' => $weight->multiply($quantity),
        'declared_value' => $order_item->getUnitPrice()->multiply($quantity),
      ]);
    }

    $proposed_shipments = [];
    if (!empty($items)) {
      $proposed_shipments[] = new ProposedShipment([
        'type' => $this->getShipmentType($order),
        'order_id' => $order->id(),
        'title' => t('Shipment #1'),
        'items' => $items,
        'shipping_profile' => $shipping_profile,
      ]);
    }

    return $proposed_shipments;
  }

  /**
   * Gets the shipment type for the current order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return string
   *   The shipment type.
   */
  protected function getShipmentType(OrderInterface $order) {
    $order_type_storage = $this->entityTypeManager->getStorage('commerce_order_type');
    /** @var \Drupal\commerce_order\Entity\OrderTypeInterface $order_type */
    $order_type = $order_type_storage->load($order->bundle());

    return $order_type->getThirdPartySetting('commerce_shipping', 'shipment_type');
  }

}
