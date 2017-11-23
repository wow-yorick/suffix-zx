<?php

namespace Drupal\physical_test\Form;

use Drupal\physical\Length;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\physical\LengthUnit;

class DimensionsTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'physical_dimensions_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $restrict_unit = FALSE) {
    $form['dimensions'] = [
      '#type' => 'physical_dimensions',
      '#title' => $this->t('Dimensions'),
      '#default_value' => [
        'length' => '1.92',
        'width' => '2.5',
        'height' => '2.1',
        'unit' => LengthUnit::METER
      ],
      '#required' => TRUE,
    ];
    if ($restrict_unit) {
      $form['dimensions']['#available_units'] = [LengthUnit::METER];
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
    // Create Length objects to ensure the values are valid.
    $value = $form_state->getValue('dimensions');
    $length = new Length($value['length'], $value['unit']);
    $width = new Length($value['width'], $value['unit']);
    $height = new Length($value['height'], $value['unit']);
    drupal_set_message(t('Length: "@length", width: "@width", height: "@height", unit: "@unit".', [
      '@length' => $length->getNumber(),
      '@width' => $width->getNumber(),
      '@height' => $height->getNumber(),
      '@unit' => $height->getUnit(),
    ]));
  }

}
