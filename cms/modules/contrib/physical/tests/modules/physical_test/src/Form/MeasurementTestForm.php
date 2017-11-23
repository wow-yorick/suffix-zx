<?php

namespace Drupal\physical_test\Form;

use Drupal\physical\Length;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\physical\LengthUnit;

class MeasurementTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'physical_measurement_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $restrict_unit = FALSE) {
    $form['height'] = [
      '#type' => 'physical_measurement',
      '#measurement_type' => 'length',
      '#title' => $this->t('Height'),
      '#default_value' => ['number' => '1.92', 'unit' => LengthUnit::METER],
      '#required' => TRUE,
    ];
    if ($restrict_unit) {
      $form['height']['#available_units'] = [LengthUnit::METER];
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Create a Length object to ensure the values are valid.
    $value = $form_state->getValue('height');
    $height = new Length($value['number'], $value['unit']);
    drupal_set_message(t('The number is "@number" and the unit is "@unit".', [
      '@number' => $height->getNumber(),
      '@unit' => $height->getUnit(),
    ]));
  }

}
