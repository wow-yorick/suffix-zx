<?php

namespace Drupal\physical\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'physical_measurement_default' formatter.
 *
 * @FieldFormatter(
 *   id = "physical_measurement_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "physical_measurement"
 *   }
 * )
 */
class MeasurementDefaultFormatter extends PhysicalFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    /** @var \Drupal\physical\Plugin\Field\FieldType\MeasurementItem $item */
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#markup' => $this->numberFormatter->format($item->number) . ' ' . $item->unit,
      ];
    }

    return $element;
  }

}
