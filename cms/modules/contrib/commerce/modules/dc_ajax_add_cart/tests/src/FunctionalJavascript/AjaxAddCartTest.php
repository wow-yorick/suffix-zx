<?php

namespace Drupal\Tests\dc_ajax_add_cart\FunctionalJavascript;

use Drupal\commerce_order\Entity\Order;
use Drupal\Tests\dc_ajax_add_cart\Functional\AjaxAddCartTestBase;

/**
 * Ajax add cart tests.
 *
 * @ingroup dc_ajax_add_cart
 *
 * @group dc_ajax_add_cart
 */
class AjaxAddCartTest extends AjaxAddCartTestBase {

  /**
   * Tests add to cart form.
   */
  public function testAddCartForm() {
    $this->drupalGet("product/{$this->variation->getProduct()->id()}");
    $ajax_add_cart_button = $this->getSession()->getPage()->findButton('Add to cart');

    /*
     * Confirm that the initial add to cart submit works.
     */
    $ajax_add_cart_button->click();
    $this->waitForAjaxToFinish();

    $this->cart = Order::load($this->cart->id());
    $order_items = $this->cart->getItems();
    $this->assertOrderItemInOrder($this->variation, $order_items[0]);

    /*
     * Confirm that the second add to cart submit increments the quantity
     * of the first order item.
     */
    $ajax_add_cart_button->click();
    $this->waitForAjaxToFinish();

    \Drupal::entityTypeManager()->getStorage('commerce_order')->resetCache();
    \Drupal::entityTypeManager()->getStorage('commerce_order_item')->resetCache();
    $this->cart = Order::load($this->cart->id());
    $order_items = $this->cart->getItems();
    $this->assertNotEmpty(count($order_items) == 1, 'No additional order items were created');
    $this->assertOrderItemInOrder($this->variation, $order_items[0], 2);
  }

  /**
   * Tests whether the add to cart form is indeed ajaxified.
   */
  public function testAjaxAddCartForm() {
    $this->drupalGet("product/{$this->variation->getProduct()->id()}");
    $ajax_add_cart_button = $this->getSession()->getPage()->findButton('Add to cart');

    $ajax_add_cart_button->click();

    $this->cart = Order::load($this->cart->id());
    $order_items = $this->cart->getItems();
    $this->assertEmpty($order_items, 'Order items found in the cart');
  }

}
