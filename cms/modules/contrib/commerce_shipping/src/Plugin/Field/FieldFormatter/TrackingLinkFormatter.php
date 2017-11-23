<?php

namespace Drupal\commerce_shipping\Plugin\Field\FieldFormatter;

use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\SupportsTrackingInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'commerce_tracking_link' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_tracking_link",
 *   label = @Translation("Tracking link"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class TrackingLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if ($items->isEmpty()) {
      return [];
    }
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = $items[0]->getEntity();
    $shipping_method = $shipment->getShippingMethod();
    if (!$shipping_method) {
      return [];
    }
    /** @var \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\SupportsTrackingInterface $shipping_method_plugin */
    $shipping_method_plugin = $shipment->getShippingMethod()->getPlugin();
    if (!($shipping_method_plugin instanceof SupportsTrackingInterface)) {
      return [];
    }
    $tracking_url = $shipping_method_plugin->getTrackingUrl($shipment);
    if (!$tracking_url) {
      return [];
    }
    $elements = [];
    $elements[] = [
      '#type' => 'link',
      '#title' => $this->t('Tracking link'),
      '#url' => $tracking_url,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $entity_type == 'commerce_shipment' && $field_name == 'tracking_code';
  }

}
