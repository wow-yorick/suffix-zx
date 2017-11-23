<?php

namespace Drupal\physical\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\physical\MeasurementType;

/**
 * Plugin implementation of the 'physical_measurement_default' widget.
 *
 * @FieldWidget(
 *   id = "physical_measurement_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "physical_measurement"
 *   }
 * )
 */
class MeasurementDefaultWidget extends PhysicalWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default_unit = $this->getDefaultUnit();
    if ($items[$delta]->isEmpty()) {
      $items[$delta]->number = NULL;
      $items[$delta]->unit = $default_unit;
    }

    $element = [
      '#type' => 'physical_measurement',
      '#measurement_type' => $this->fieldDefinition->getSetting('measurement_type'),
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
    $measurement_type = $this->fieldDefinition->getSetting('measurement_type');
    return MeasurementType::getUnitClass($measurement_type);
  }

}
