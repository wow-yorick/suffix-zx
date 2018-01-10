<?php

namespace Drupal\commerce_customization\Plugin\CommerceCustomization;

use Drupal\commerce_customization\Plugin\CommerceCustomizationBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_price\Entity\Currency;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Entity\File as CoreFile;

/**
 * Implements a textarea widget for customizations.
 *
 * @CommerceCustomization(
 *  id = "file",
 *  label = @Translation("File"),
 * )
 */
class File extends CommerceCustomizationBase {

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
    $widget['extensions'] = [
      '#type' => 'textfield',
      '#title' => t('Accepted file extensions'),
      '#default_value' => isset($field_settings['extensions']) ? $field_settings['extensions'] : 0,
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
    $widget['file'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://customizations',
      '#upload_validators' => [
        'file_validate_extensions' => $field_settings['extensions'],
      ],
    ];
    $widget['file_description'] = [
      '#suffix' => t('Accepted extensions: @extensions', ['@extensions' => $field_settings['extensions']]),
    ];

    // We only need 1 of these handlers in the form.
    if (!in_array([$this, 'makeFilePermanent'], $form['actions']['submit']['#submit'])) {
      $form['actions']['submit']['#submit'][] = [$this, 'makeFilePermanent'];
    }
    return $widget;
  }

  /**
   * Makes the uploaded file permanent.
   */
  public function makeFilePermanent($form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('commerce_customization') as $customization) {
      if ($customization['__plugin'] == 'file') {
        $fid = reset($customization['file']);
        // Core file is the Drupal File entity.
        if (is_numeric($fid) && $file = CoreFile::load($fid)) {
          $file->setPermanent();
          $file->save();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePrice($customization_data) {
    if (count($customization_data['file'])) {
      $field_settings = $customization_data['__settings'];
      return $field_settings['price']['number'];
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function render($customization_data) {
    $field_settings = $customization_data['__settings'];
    if (!count($customization_data['file'])) {
      return NULL;
    }

    $render = [];
    $file = CoreFile::load($customization_data['file'][0]);
    $url = file_create_url($file->getFileUri());
    $render['link'] = [
      '#type' => 'item',
      '#markup' => "<a href='{$url}' target='_blank'>Download file</a>",
      '#title' => $field_settings['title'],
    ];
    // @todo let developers alter this.
    return $render;
  }

}
