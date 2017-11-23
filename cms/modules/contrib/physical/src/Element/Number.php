<?php

namespace Drupal\physical\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element;

/**
 * Provides a number form element with support for language-specific input.
 *
 * The #default_value is given in the generic, language-agnostic format, which
 * is then formatted into the language-specific format on element display.
 * During element validation the input is converted back into to the generic
 * format, to allow the returned value to be stored.
 *
 * Usage example:
 * @code
 * $form['number'] = [
 *   '#type' => 'physical_number',
 *   '#title' => t('Number'),
 *   '#default_value' => '18.99',
 *   '#required' => TRUE,
 * ];
 * @endcode
 *
 * @FormElement("physical_number")
 */
class Number extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#min' => 0,
      '#max' => NULL,

      '#size' => 10,
      '#maxlength' => 128,
      '#default_value' => NULL,
      '#element_validate' => [
        [$class, 'validateNumber'],
      ],
      '#process' => [
        [$class, 'processElement'],
        [$class, 'processAjaxForm'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderNumber'],
        [$class, 'preRenderGroup'],
      ],
      '#input' => TRUE,
      '#theme' => 'input__textfield',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE && $input !== NULL) {
      if (!is_scalar($input)) {
        $input = '';
      }
      return trim($input);
    }
    elseif (!empty($element['#default_value'])) {
      // Convert the stored number to the local format. For example, "9.99"
      // becomes "9,99" in many locales. This also strips any extra zeroes.
      $number_formatter = \Drupal::service('physical.number_formatter');
      return $number_formatter->format($element['#default_value']);
    }

    return NULL;
  }

  /**
   * Builds the physical_number form element.
   *
   * @param array $element
   *   The initial physical_number form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The built physical_number form element.
   */
  public static function processElement(array $element, FormStateInterface $form_state, &$complete_form) {
    // Provide an example to the end user so that they know which decimal
    // separator to use. This is the same pattern Drupal core uses.
    $number_formatter = \Drupal::service('physical.number_formatter');
    $element['#placeholder'] = $number_formatter->format('9.99');

    return $element;
  }

  /**
   * Validates the number element.
   *
   * Converts the number back to the standard format (e.g. "9,99" -> "9.99").
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function validateNumber(array $element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    if ($value === '') {
      return;
    }
    $title = empty($element['#title']) ? $element['#parents'][0] : $element['#title'];
    $number_formatter = \Drupal::service('physical.number_formatter');

    $value = $number_formatter->parse($value);
    if ($value === FALSE) {
      $form_state->setError($element, t('%title must be a number.', [
        '%title' => $title,
      ]));
      return;
    }
    if (isset($element['#min']) && $value < $element['#min']) {
      $form_state->setError($element, t('%title must be higher than or equal to %min.', [
        '%title' => $title,
        '%min' => $element['#min'],
      ]));
      return;
    }
    if (isset($element['#max']) && $value > $element['#max']) {
      $form_state->setError($element, t('%title must be lower than or equal to %max.', [
        '%title' => $title,
        '%max' => $element['#max'],
      ]));
      return;
    }

    $form_state->setValueForElement($element, $value);
  }

  /**
   * Prepares a #type 'physical_number' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderNumber(array $element) {
    // We're not using the "number" type because it won't accept
    // language-specific input, such as commas.
    $element['#attributes']['type'] = 'text';
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']);
    static::setAttributes($element, ['form-text']);

    return $element;
  }

}
