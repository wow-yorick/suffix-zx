<?php

namespace Drupal\commerce_customization\Plugin;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for Commerce customization plugins.
 */
abstract class CommerceCustomizationBase extends PluginBase implements CommerceCustomizationInterface {

  /**
   * Returns the configuration form.
   */
  public function getConfigForm(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state, $field_settings) {
    $widget = [];
    $widget['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => isset($field_settings['title']) ? $field_settings['title'] : '',
    ];
    return $widget;
  }

  /**
   * Returns the form presented to the client when buying a product.
   */
  public function getCustomizationForm(&$form, FormStateInterface $form_state, $field_settings) {
    return [];
  }

  /**
   * Return the price that should be add to the order item.
   */
  public function calculatePrice($data) {
    return 0;
  }

  /**
   * Returns a render array given data or NULL to not print anything.
   */
  public function render($data) {
    return NULL;
  }

  /**
   * Modify data as needed.
   */
  public function massageFormValues($data) {
    return $data;
  }

}
