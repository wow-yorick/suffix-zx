<?php

namespace Drupal\Tests\dc_ajax_add_cart\Functional;

use Drupal\Tests\commerce\FunctionalJavascript\JavascriptTestTrait;
use Drupal\Tests\commerce_cart\Functional\CartBrowserTestBase;

/**
 * Base class for ajax add cart tests.
 */
abstract class AjaxAddCartTestBase extends CartBrowserTestBase {

  use JavascriptTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_product',
    'commerce_cart',
    'dc_ajax_add_cart',
    'dc_ajax_add_cart_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Change commerce_product variation view display to dc_ajax_add_cart.
    \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('commerce_product.default.default')
      ->setComponent('variations', [
        'type' => 'dc_ajax_add_cart',
        'settings' => [
          'default_quantity' => '1',
          'combine' => TRUE,
          'show_quantity' => FALSE,
        ],
        'weight' => 0,
        'label' => 'hidden',
        'region' => 'content',
      ])
      ->save();
  }

}
