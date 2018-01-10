<?php

namespace Drupal\dc_ajax_add_cart\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\commerce_product\Plugin\Field\FieldFormatter\AddToCartFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dc_ajax_add_cart' formatter.
 *
 * @FieldFormatter(
 *   id = "dc_ajax_add_cart",
 *   label = @Translation("Ajax add to cart form"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class AjaxAddToCartFormatter extends AddToCartFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['combine'] = [
      '#type' => 'checkbox',
      '#title' => t('Combine order items containing the same product variation.'),
      '#description' => t('The order item type, referenced product variation, and data from fields exposed on the Ajax Add to Cart form must all match to combine.'),
      '#default_value' => $this->getSetting('combine'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $elements[0]['dc_ajax_add_cart_form'] = [
      '#lazy_builder' => [
        'dc_ajax_add_cart.lazy_builders:ajaxAddToCartForm', [
          $items->getEntity()->id(),
          $this->viewMode,
          $this->getSetting('combine'),
        ],
      ],
      '#create_placeholder' => TRUE,
    ];
    return $elements;
  }

}
