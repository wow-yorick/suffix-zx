<?php

namespace Drupal\commerce_shipping_test\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Creates a shipment per order item.
 *
 * Only applies to shipments going to France.
 */
class TestPacker implements PackerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(OrderInterface $order, ProfileInterface $shipping_profile) {
    return $shipping_profile->address->country_code == 'FR';
  }

  /**
   * {@inheritdoc}
   */
  public function pack(OrderInterface $order, ProfileInterface $shipping_profile) {
    $proposed_shipments = [];
    foreach ($order->getItems() as $index => $order_item) {
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
      $proposed_shipments[] = new ProposedShipment([
        'type' => 'default',
        'order_id' => $order->id(),
        'title' => t('Shipment #@index', ['@index' => $index + 1]),
        'items' => [
          new ShipmentItem([
            'order_item_id' => $order_item->id(),
            'title' => $order_item->getTitle(),
            'quantity' => $quantity,
            'weight' => $weight->multiply($quantity),
            'declared_value' => $order_item->getUnitPrice()->multiply($quantity),
          ]),
        ],
        'shipping_profile' => $shipping_profile,
      ]);
    }

    return $proposed_shipments;
  }

}
