<?php

namespace Drupal\commerce_shipping\Form;

use Drupal\commerce\Form\CommerceBundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeInterface;

class ShipmentTypeForm extends CommerceBundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\commerce_shipping\Entity\ShipmentTypeInterface $shipment_type */
    $shipment_type = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $shipment_type->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $shipment_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_shipping\Entity\ShipmentType::load',
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
    ];
    $form = $this->buildTraitForm($form, $form_state);

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateTraitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
    $this->submitTraitForm($form, $form_state);

    drupal_set_message($this->t('Saved the %label shipment type.', [
      '%label' => $this->entity->label(),
    ]));
    $form_state->setRedirect('entity.commerce_shipment_type.collection');
  }

}
