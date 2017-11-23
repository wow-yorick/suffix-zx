<?php

namespace Drupal\physical\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'physical_dimensions_default' formatter.
 *
 * @FieldFormatter(
 *   id = "physical_dimensions_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "physical_dimensions"
 *   }
 * )
 */
class DimensionsDefaultFormatter extends PhysicalFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    /** @var \Drupal\physical\Plugin\Field\FieldType\DimensionItem $item */
    foreach ($items as $delta => $item) {
      $dimensions = [
        $this->numberFormatter->format($item->length),
        $this->numberFormatter->format($item->width),
        $this->numberFormatter->format($item->height),
      ];
      $element[$delta] = [
        '#markup' => implode(' &times; ', $dimensions) . ' ' . $item->unit,
      ];
    }

    return $element;
  }

}
