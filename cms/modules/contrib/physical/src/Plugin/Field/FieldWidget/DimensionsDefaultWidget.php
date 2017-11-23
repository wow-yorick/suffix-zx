<?php

namespace Drupal\physical\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\physical\LengthUnit;

/**
 * Plugin implementation of the 'physical_dimensions_default' widget.
 *
 * @FieldWidget(
 *   id = "physical_dimensions_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "physical_dimensions"
 *   },
 * )
 */
class DimensionsDefaultWidget extends PhysicalWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default_unit = $this->getDefaultUnit();
    if ($items[$delta]->isEmpty()) {
      $items[$delta]->length = NULL;
      $items[$delta]->width = NULL;
      $items[$delta]->height = NULL;
      $items[$delta]->unit = $default_unit;
    }

    $element = [
      '#type' => 'physical_dimensions',
      '#allow_unit_change' => $this->getSetting('allow_unit_change'),
      '#default_value' => $items[$delta]->getValue(),
    ] + $element;
    if (!$this->getSetting('allow_unit_change')) {
      $element['#available_units'] = [$default_unit];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getUnitClass() {
    return LengthUnit::class;
  }

}
