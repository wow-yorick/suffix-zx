<?php

namespace Drupal\commerce_shipping\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'commerce_shipping_method' formatter.
 *
 * Represents the shipping method using the label of the selected service.
 *
 * @FieldFormatter(
 *   id = "commerce_shipping_method",
 *   label = @Translation("Shipping method"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ShippingMethodFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = $items[0]->getEntity();
    $shipping_service_id = $shipment->getShippingService();

    $elements = [];
    foreach ($items as $delta => $item) {
      /** @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface $shipping_method */
      $shipping_method = $item->entity;
      $shipping_services = $shipping_method->getPlugin()->getServices();
      if (isset($shipping_services[$shipping_service_id])) {
        $elements[$delta] = [
          '#markup' => $shipping_services[$shipping_service_id]->getLabel(),
        ];
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $entity_type == 'commerce_shipment' && $field_name == 'shipping_method';
  }

}
