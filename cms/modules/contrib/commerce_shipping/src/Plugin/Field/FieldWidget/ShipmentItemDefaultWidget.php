<?php

namespace Drupal\commerce_shipping\Plugin\Field\FieldWidget;

use Drupal\commerce_shipping\ShipmentItem;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of 'commerce_shipment_item_default'.
 *
 * @FieldWidget(
 *   id = "commerce_shipment_item_default",
 *   label = @Translation("Shipment Item"),
 *   field_types = {
 *     "commerce_shipment_item"
 *   }
 * )
 */
class ShipmentItemDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => $value) {
      $values[$key] = new ShipmentItem([
        'purchased_entity_id' => $value['purchased_entity_id'],
        'purchased_entity_type' => $value['purchased_entity_type'],
        'quantity' => $value['quantity'],
        'order_item_id' => $value['order_item_id'],
      ]);
    }
    return $values;
  }

}
