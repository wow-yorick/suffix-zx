<?php

namespace Drupal\commerce_price_rule\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the price list deletion form.
 *
 * Allows to delete a list only if there are no price rules associated with it
 * and it does not contain any items.
 */
class PriceListDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Check if there are rules associated with the list.
    // The list id is stored in the calculation plugin configuration and we
    // cannot query this directly. We therefore need to load all rules that
    // contain calculations of 'price_list' type, and check if there is at least
    // one that is associated with the list we are deleting.
    $price_rule_ids = \Drupal::entityQuery('commerce_price_rule')
      ->condition('calculation__target_plugin_id', 'price_list')
      ->execute();

    if (!$price_rule_ids) {
      return parent::buildForm($form, $form_state);
    }

    $price_rules = $this->entityManager
      ->getStorage('commerce_price_rule')
      ->loadMultiple($price_rule_ids);

    $rule_exists = FALSE;
    $price_list_id = $this->getEntity()->id();

    foreach ($price_rules as $price_rule) {
      $calculation_configuration = $price_rule->getCalculation()->getConfiguration();
      if ($calculation_configuration['price_list_id'] == $price_list_id) {
        $rule_exists = TRUE;
        break;
      }
    }

    if ($rule_exists) {
      $form['cannot_delete'] = [
        '#markup' => $this->t('You cannot delete this price list because it is currently associated with one or more price rules. You should remove any associations by editing the price rules that use this price list before trying again.'),
      ];

      return $form;
    }

    // Next, we have to check whether there are items in the list. We could
    // provide functionality here to delete all items upon form submission, but
    // it's best the user explicitly deletes them himself/herself to avoid not
    // reading the warning and not understanding the consequence. The Manage
    // Items page should allow bulk-deleting to make this easier.
    $list_item_count = \Drupal::entityQuery('commerce_price_rule_list_item')
      ->condition('price_list_id', $price_list_id)
      ->count()
      ->execute();

    if ($list_item_count) {
      $form['cannot_delete'] = [
        '#markup' => $this->t('You can only delete an empty price list. Please delete all list items or move them to another list and try again.'),
      ];

      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

}
