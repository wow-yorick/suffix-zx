<?php

namespace Drupal\commerce_shipping\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\physical\MeasurementType;

class PackageTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\commerce_shipping\Entity\PackageTypeInterface $package_type */
    $package_type = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $package_type->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $package_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_shipping\Entity\PackageType::load',
      ],
      '#disabled' => !$package_type->isNew(),
    ];
    $form['dimensions'] = [
      '#type' => 'physical_dimensions',
      '#title' => $this->t('Dimensions'),
      '#default_value' => $package_type->getDimensions(),
      '#required' => TRUE,
    ];
    $weight = $package_type->getWeight();
    $form['weight'] = [
      '#type' => 'physical_measurement',
      '#title' => $this->t('Weight'),
      '#measurement_type' => MeasurementType::WEIGHT,
      '#default_value' => $weight ?: ['number' => 0, 'unit' => 'g'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $package_type = $this->entity;
    $status = $package_type->save();
    // Clear the plugin cache to expose the new/modified entity as a plugin.
    \Drupal::service('plugin.manager.commerce_package_type')->clearCachedDefinitions();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label package type.', [
          '%label' => $package_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label package type.', [
          '%label' => $package_type->label(),
        ]));
    }
    $form_state->setRedirect('entity.commerce_package_type.collection');
  }

}
