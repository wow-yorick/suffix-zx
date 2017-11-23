<?php

namespace Drupal\commerce_shipping\Plugin\Field\FieldType;

use Drupal\commerce_shipping\ShipmentItem as ShipmentItemValue;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'commerce_shipment_item' field type.
 *
 * @FieldType(
 *   id = "commerce_shipment_item",
 *   label = @Translation("Shipment Item"),
 *   description = @Translation("Stores shipment items."),
 *   category = @Translation("Commerce"),
 *   list_class = "\Drupal\commerce_shipping\Plugin\Field\FieldType\ShipmentItemList",
 *   no_ui = TRUE,
 *   default_widget = "commerce_shipment_item_default",
 * )
 */
class ShipmentItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('any')
      ->setLabel(t('Value'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return $this->value === NULL || !$this->value instanceof ShipmentItemValue;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (is_array($values)) {
      // The property definition causes the shipment item to be in 'value' key.
      $values = reset($values);
    }
    if (!$values instanceof ShipmentItemValue) {
      $values = NULL;
    }
    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'description' => 'The shipment item value.',
          'type' => 'blob',
          'not null' => TRUE,
          'serialize' => TRUE,
        ],
      ],
    ];
  }

}
