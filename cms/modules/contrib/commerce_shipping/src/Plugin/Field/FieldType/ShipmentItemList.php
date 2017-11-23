<?php

namespace Drupal\commerce_shipping\Plugin\Field\FieldType;

use Drupal\commerce_shipping\ShipmentItem as ShipmentItemValue;
use Drupal\Core\Field\FieldItemList;

/**
 * Represents a list of shipment item field values.
 */
class ShipmentItemList extends FieldItemList implements ShipmentItemListInterface {

  /**
   * {@inheritdoc}
   */
  public function getShipmentItems() {
    $shipment_items = [];
    /** @var \Drupal\commerce_shipping\Plugin\Field\FieldType\ShipmentItem $field_item */
    foreach ($this->list as $key => $field_item) {
      $shipment_items[$key] = $field_item->value;
    }

    return $shipment_items;
  }

  /**
   * {@inheritdoc}
   */
  public function removeShipmentItem(ShipmentItemValue $shipment_item) {
    /** @var \Drupal\commerce_shipping\Plugin\Field\FieldType\ShipmentItem $field_item */
    foreach ($this->list as $key => $field_item) {
      if ($field_item->value === $shipment_item) {
        $this->removeItem($key);
      }
    }
  }

}
