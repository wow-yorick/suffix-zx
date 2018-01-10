<?php

namespace Drupal\commerce_price_rule\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the price list add/edit form.
 */
class PriceListForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
    drupal_set_message(
      $this->t(
        'Saved the %label price list.',
        ['%label' => $this->entity->label()]
      )
    );
  }

}
