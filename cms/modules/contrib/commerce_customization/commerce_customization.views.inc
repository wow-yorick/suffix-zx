<?php

/**
 * @file
 * Contains commerce_customization\commerce_customization.views.inc..
 */

/**
 * Implements hook_views_data().
 */
function commerce_customization_views_data() {
  $data['commerce_order_item']['commerce_customizations_field'] = [
    'title' => t('Customizations'),
    'help' => t('Customizations for a order item.'),
    'field' => [
      'id' => 'commerce_customizations_field',
    ],
  ];

  return $data;
}
