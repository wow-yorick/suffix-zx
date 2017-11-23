<?php

namespace Drupal\physical\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\physical\LengthUnit;

/**
 * Provides a dimension form element.
 *
 * The size and maxlength properties are applied to each textfield individually.
 *
 * Usage example:
 * @code
 * $form['dimensions'] = [
 *   '#type' => 'physical_dimensions',
 *   '#title' => $this->t('Dimensions'),
 *   '#default_value' => [
 *     'length' => '1.90',
 *     'width' => '2.5',
 *     'height' => '2.1',
 *     'unit' => LengthUnit::METER
 *   ],
 *   '#size' => 60,
 *   '#maxlength' => 128,
 *   '#required' => TRUE,
 * ];
 * @endcode
 *
 * @FormElement("physical_dimensions")
 */
class Dimensions extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
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
      '#theme_wrappers' => ['fieldset'],
    ];
  }

  /**
   * Builds the physical_dimensions form element.
   *
   * @param array $element
   *   The initial physical_dimensions form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The built physical_dimensions form element.
   *
   * @throws \InvalidArgumentException.
   */
  public static function processElement(array $element, FormStateInterface $form_state, &$complete_form) {
    if (!is_array($element['#available_units'])) {
      throw new \InvalidArgumentException('The #available_units key must be an array.');
    }
    $default_value = $element['#default_value'];
    if (isset($default_value)) {
      if (!self::validateDefaultValue($default_value)) {
        throw new \InvalidArgumentException('The #default_value for a physical_dimensions element must be an array with "length", "width", "height", "unit" keys.');
      }
      LengthUnit::assertExists($default_value['unit']);
    }

    $element['#tree'] = TRUE;
    $element['#attributes']['class'][] = 'form-type-physical-dimensions';

    $properties = [
      'length' => t('Length'),
      'width' => t('Width'),
      'height' => t('Height'),
    ];
    foreach ($properties as $property => $label) {
      $element[$property] = [
        '#type' => 'physical_number',
        '#title' => $label,
        '#default_value' => $default_value ? $default_value[$property] : NULL,
        '#size' => $element['#size'],
        '#maxlength' => $element['#maxlength'],
        '#required' => $element['#required'],
        '#field_suffix' => '&times;',
      ];
    }
    unset($element['height']['#field_suffix']);
    unset($element['#size']);
    unset($element['#maxlength']);

    $units = LengthUnit::getLabels();
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
      $last_visible_element = 'height';
      $unit_keys = array_keys($units);
      $unit = reset($unit_keys);
      $element['unit'] = [
        '#type' => 'value',
        '#value' => $unit,
      ];
      // Display the unit as a text element after the textfield.
      $element['height']['#field_suffix'] = $unit;
    }
    else {
      $last_visible_element = 'unit';
      $element['unit'] = [
        '#type' => 'select',
        '#options' => $units,
        '#default_value' => $default_value ? $default_value['unit'] : LengthUnit::getBaseUnit(),
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
    foreach (['length', 'width', 'height', 'unit'] as $property) {
      if (!array_key_exists($property, $default_value)) {
        return FALSE;
      }
    }
    return TRUE;
  }

}
