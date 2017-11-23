<?php

namespace Drupal\commerce_shipping\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds the form to delete a shipment type.
 */
class ShipmentTypeDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $shipment_query = $this->entityTypeManager->getStorage('commerce_shipment')->getQuery();
    $shipment_count = $shipment_query
      ->condition('type', $this->entity->id())
      ->count()
      ->execute();
    if ($shipment_count) {
      $caption = '<p>' . $this->formatPlural($shipment_count, '%type is used by 1 shipment on your site. You can not remove this shipment type until you have removed all of the %type shipments.', '%type is used by @count shipments on your site. You may not remove %type until you have removed all of the %type shipments.', ['%type' => $this->entity->label()]) . '</p>';
      $form['#title'] = $this->getQuestion();
      $form['description'] = ['#markup' => $caption];
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

}
