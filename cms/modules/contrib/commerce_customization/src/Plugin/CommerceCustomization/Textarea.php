<?php

namespace Drupal\commerce_customization\Plugin\CommerceCustomization;

use Drupal\commerce_customization\Plugin\CommerceCustomizationBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_price\Entity\Currency;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Implements a textarea widget for customizations.
 *
 * @CommerceCustomization(
 *  id = "textarea",
 *  label = @Translation("Textarea"),
 * )
 */
class Textarea extends CommerceCustomizationBase {

  /**
   * {@inheritdoc}
   */
  public function getConfigForm(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state, $field_settings) {
    $widget = parent::getConfigForm($items, $delta, $element, $form, $form_state, $field_settings);
    $store = \Drupal::service('commerce_store.current_store')->getStore();
    $currency_code = $store->getDefaultCurrencyCode();

    $widget['price'] = [
      '#type' => 'commerce_price',
      '#title' => t('Price'),
      '#default_value' => isset($field_settings['price']) ? $field_settings['price'] : ['number' => 0, 'currency_code' => $currency_code],
    ];
    $widget['max_characters'] = [
      '#type' => 'number',
      '#title' => t('Max characters'),
      '#default_value' => isset($field_settings['max_characters']) ? $field_settings['max_characters'] : 0,
    ];
    return $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomizationForm(&$form, FormStateInterface $form_state, $field_settings) {
    $widget = [];
    $title = isset($field_settings['title']) ? $field_settings['title'] : '';

    // Print price.
    $price = $field_settings['price'];
    $currency_code = $price['currency_code'];
    $currency = Currency::load($currency_code);
    $widget['customization_price'] = [
      '#theme' => 'commerce_customization_title',
      '#currency' => $currency,
      '#number' => $price['number'],
      '#title' => $title,
    ];
    $widget['text'] = [
      '#type' => 'textarea',
    ];
    if ($field_settings['max_characters'] > 0) {
      $widget['text']['#suffix'] = t('Max characters is @max.', ['@max' => $field_settings['max_characters']]);
    }

    return $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePrice($customization_data) {
    // The field settings is serialized, so we don't lose the original data if
    // the product is edited.
    $field_settings = $customization_data['__settings'];
    if (!empty(trim($customization_data['text']))) {
      return $field_settings['price']['number'];
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function render($customization_data) {
    $field_settings = $customization_data['__settings'];
    if (empty(trim($customization_data['text']))) {
      return NULL;
    }
    $render = [];
    $render['text'] = [
      '#type' => 'item',
      '#markup' => $customization_data['text'],
      '#title' => $field_settings['title'],
    ];
    // @todo let developers alter this.
    return $render;
  }

}
