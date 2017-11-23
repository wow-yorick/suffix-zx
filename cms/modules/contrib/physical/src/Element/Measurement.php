<?php

namespace Drupal\physical\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\physical\MeasurementType;

/**
 * Provides a measurement form element.
 *
 * Usage example:
 * @code
 * $form['height'] = [
 *   '#type' => 'physical_measurement',
 *   '#measurement_type' => 'length',
 *   '#title' => $this->t('Height'),
 *   '#default_value' => ['number' => '1.90', 'unit' => LengthUnit::METER],
 *   '#size' => 60,
 *   '#maxlength' => 128,
 *   '#required' => TRUE,
 * ];
 * @endcode
 *
 * @FormElement("physical_measurement")
 */
class Measurement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#measurement_type' => NULL,
      // All units are available by default.
      '#available_units' => [],

      '#size' => 10,
      '#maxlength' => 128,
      '#default_value' => NULL,
      '#attached' => [
        'library' => ['physical/admin'],
      ],
      '#process' => [
        [$class, 'processElement'],
        [$class, 'processAjaxForm'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
      ],
      '#input' => TRUE,
      '#theme_wrappers' => ['container'],
    ];
  }

  /**
   * Builds the physical_measurement form element.
   *
   * @param array $element
   *   The initial physical_measurement form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The built physical_measurement form element.
   *
   * @throws \InvalidArgumentException.
   */
  public static function processElement(array $element, FormStateInterface $form_state, &$complete_form) {
    if (empty($element['#measurement_type'])) {
      throw new \InvalidArgumentException('The #measurement_type must be defined for a physical_measurement element.');
    }
    if (!is_array($element['#available_units'])) {
      throw new \InvalidArgumentException('The #available_units key must be an array.');
    }
    /** @var \Drupal\physical\UnitInterface $unit_class */
    $unit_class = MeasurementType::getUnitClass($element['#measurement_type']);
    $default_value = $element['#default_value'];
    if (isset($default_value)) {
      if (!self::validateDefaultValue($default_value)) {
        throw new \InvalidArgumentException('The #default_value for a physical_measurement element must be an array with "number" and "unit" keys.');
      }
      $unit_class::assertExists($default_value['unit']);
    }

    $element['#tree'] = TRUE;
    $element['#attributes']['class'][] = 'form-type-physical-measurement';

    $element['number'] = [
      '#type' => 'physical_number',
      '#title' => $element['#title'],
      '#default_value' => $default_value ? $default_value['number'] : NULL,
      '#required' => $element['#required'],
      '#size' => $element['#size'],
      '#maxlength' => $element['#maxlength'],
    ];
    unset($element['#size']);
    unset($element['#maxlength']);

    $units = $unit_class::getLabels();
    // Restrict the list of available units, if needed.
    if ($element['#available_units']) {
      $available_units = $element['#available_units'];
      // The current unit should always be available.
      if ($default_value) {
        $available_units[] = $default_value['unit'];
      }
      $available_units = array_combine($available_units, $available_units);
      $units = array_intersect_key($units, $available_units);
    }

    if (count($units) === 1) {
      $last_visible_element = 'number';
      $unit_keys = array_keys($units);
      $unit = reset($unit_keys);
      $element['unit'] = [
        '#type' => 'value',
        '#value' => $unit,
      ];
      // Display the unit as a text element after the textfield.
      $element['number']['#field_suffix'] = $unit;
    }
    else {
      $last_visible_element = 'unit';
      $element['unit'] = [
        '#type' => 'select',
        '#options' => $units,
        '#default_value' => $default_value ? $default_value['unit'] : $unit_class::getBaseUnit(),
        '#title_display' => 'invisible',
      ];
    }

    // Add the help text if specified.
    if (!empty($element['#description'])) {
      $element[$last_visible_element]['#field_suffix'] .= '<div class="description">' . $element['#description'] . '</div>';
    }

    return $element;
  }

  /**
   * Validates the default value.
   *
   * @param mixed $default_value
   *   The default value.
   *
   * @return bool
   *   TRUE if the default value is valid, FALSE otherwise.
   */
  public static function validateDefaultValue($default_value) {
    if (!is_array($default_value)) {
      return FALSE;
    }
    if (!array_key_exists('number', $default_value) || !array_key_exists('unit', $default_value)) {
      return FALSE;
    }
    return TRUE;
  }

}
