<?php

namespace Drupal\commerce_customization\Plugin\CommerceCustomization;

use Drupal\commerce_customization\Plugin\CommerceCustomizationBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\commerce_price\Entity\Currency;

/**
 * Implements a select widget for customizations.
 *
 * @CommerceCustomization(
 *  id = "select",
 *  label = @Translation("Select"),
 * )
 */
class Select extends CommerceCustomizationBase {

  /**
   * {@inheritdoc}
   */
  public function getConfigForm(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state, $field_settings) {
    $widget = parent::getConfigForm($items, $delta, $element, $form, $form_state, $field_settings);

    if ($form_state->has('commerce_customization_select_options_' . $delta)) {
      $quantity = $form_state->get('commerce_customization_select_options_' . $delta);
    }
    elseif (isset($field_settings['options']) && is_array($field_settings['options'])) {
      $quantity = count($field_settings['options']);
      $form_state->set('commerce_customization_select_options_' . $delta, $quantity);
    }
    else {
      $quantity = 1;
    }

    $id = implode('-', [
      'commerce-customization',
      $items->getName(),
      $delta,
      'select',
    ]);

    $widget['display_as'] = [
      '#type' => 'select',
      '#options' => [
        'select' => t('Select'),
        'radios' => t('Radios'),
      ],
      '#default_value' => isset($field_settings['display_as']) ? $field_settings['display_as'] : '',
      '#title' => t('Display as'),
    ];
    $widget['options'] = [
      '#type' => 'table',
      '#prefix' => "<div id='{$id}'>",
      '#suffix' => "</div>",
      '#header' => [t('Option title'), t('Price')],
    ];
    for ($i = 0; $i < $quantity; $i++) {
      $widget['options'][$i]['title'] = [
        '#type' => 'textfield',
        '#default_value' => isset($field_settings['options'][$i]['title']) ? $field_settings['options'][$i]['title'] : '',
        '#size' => 45,
      ];
      $widget['options'][$i]['price'] = [
        '#type' => 'commerce_price',
        '#title' => '',
        '#default_value' => isset($field_settings['options'][$i]['price']) ? $field_settings['options'][$i]['price'] : ['number' => 0, 'currency_code' => 'USD'],
      ];
    }
    $widget['add_more'] = [
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => [[$this, 'addMoreCallback']],
      '#name' => $items->getName() . "[$delta][_add_more]",
      '#ajax' => [
        'wrapper' => $id,
        'callback' => [$this, 'ajaxRefresh'],
      ],
      '#limit_validation_errors' => [],
      '#suffix' => t('To remove an item, delete the option title and save.'),
    ];
    return $widget;
  }

  /**
   * Add one more item.
   */
  public function addMoreCallback(&$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    list($field_name, $delta, $value) = explode('[', str_replace(']', '', $triggering_element['#name']));
    $items = $form_state->get('commerce_customization_select_options_' . $delta);
    if (!is_numeric($items)) {
      $items = 1;
    }
    $form_state->set('commerce_customization_select_options_' . $delta, $items + 1);
    $form_state->setRebuild();
  }

  /**
   * Add one more item.
   */
  public function ajaxRefresh($form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    list($field_name, $delta, $value) = explode('[', str_replace(']', '', $triggering_element['#name']));
    return $form[$field_name]['widget'][$delta]['data']['options'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomizationForm(&$form, FormStateInterface $form_state, $field_settings) {

    // Build the options.
    $options = array_map(function ($option) {
      $title = $option['title'];
      $number = $option['price']['number'];
      $symbol = Currency::load($option['price']['currency_code'])->getSymbol();
      return "{$title} (+{$symbol}{$number})";
    }, $field_settings['options']);

    $widget = [];
    $widget['select'] = [
      '#type' => isset($field_settings['display_as']) ? $field_settings['display_as'] : 'select',
      '#title' => isset($field_settings['title']) ? $field_settings['title'] : '',
      '#options' => $options,
    ];
    return $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePrice($customization_data) {
    $selected = $customization_data['select'];
    $price = $customization_data['__settings']['options'][$selected]['price']['number'];
    return $price;
  }

  /**
   * {@inheritdoc}
   */
  public function render($customization_data) {
    $field_settings = $customization_data['__settings'];
    $selected = $customization_data['select'];
    $render = [];
    $render['text'] = [
      '#type' => 'item',
      '#markup' => $customization_data['__settings']['options'][$selected]['title'],
      '#title' => $field_settings['title'],
    ];
    // @todo let developers alter this.
    return $render;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues($data) {
    // Remove blank items and the add_more.
    foreach ($data['options'] as $i => $option) {
      if (empty($option['title'])) {
        unset($data['options'][$i]);
      }
    }
    unset($data['add_more']);
    $data['options'] = array_values($data['options']);
    return $data;
  }

}
