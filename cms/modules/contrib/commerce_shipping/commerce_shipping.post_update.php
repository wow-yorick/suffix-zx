<?php

/**
 * @file
 * Post update functions for Shipping.
 */

/**
 * Re-save shipping methods to populate the condition operator field.
 */
function commerce_shipping_post_update_1(&$sandbox = NULL) {
  $shipping_method_storage = \Drupal::entityTypeManager()->getStorage('commerce_shipping_method');
  if (!isset($sandbox['current_count'])) {
    $query = $shipping_method_storage->getQuery();
    $sandbox['total_count'] = $query->count()->execute();
    $sandbox['current_count'] = 0;

    if (empty($sandbox['total_count'])) {
      $sandbox['#finished'] = 1;
      return;
    }
  }

  $query = $shipping_method_storage->getQuery();
  $query->range($sandbox['current_count'], 25);
  $result = $query->execute();
  if (empty($result)) {
    $sandbox['#finished'] = 1;
    return;
  }

  /** @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface[] $shipping_methods */
  $shipping_methods = $shipping_method_storage->loadMultiple($result);
  foreach ($shipping_methods as $shipping_method) {
    $shipping_method->setConditionOperator('AND');
    $shipping_method->save();
  }

  $sandbox['current_count'] += 25;
  if ($sandbox['current_count'] >= $sandbox['total_count']) {
    $sandbox['#finished'] = 1;
  }
  else {
    $sandbox['#finished'] = ($sandbox['total_count'] - $sandbox['current_count']) / $sandbox['total_count'];
  }
}
