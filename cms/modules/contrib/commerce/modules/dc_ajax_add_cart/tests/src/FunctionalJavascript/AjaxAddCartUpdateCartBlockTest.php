<?php

namespace Drupal\Tests\dc_ajax_add_cart\FunctionalJavascript;

use Drupal\commerce_order\Entity\Order;
use Drupal\Tests\dc_ajax_add_cart\Functional\AjaxAddCartTestBase;

/**
 * Ajax add cart update cart block tests.
 *
 * @TODO The default `testing` profile is unable to update the cart block. Find
 * out why this is happening, and move this test inside `AjaxAddCartTest`. If
 * you are able to do this, add another test just like `testAjaxAddCartForm()`
 * that would test whether updating of cart block is indeed ajaxified.
 *
 * @ingroup dc_ajax_add_cart
 *
 * @group dc_ajax_add_cart
 */
class AjaxAddCartUpdateCartBlockTest extends AjaxAddCartTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->placeBlock('commerce_cart');
  }

  /**
   * Tests whether the cart block is updated after product added to cart.
   */
  public function testUpdateCartBlock() {
    $this->drupalGet("product/{$this->variation->getProduct()->id()}");
    $ajax_add_cart_button = $this->getSession()->getPage()->findButton('Add to cart');

    $ajax_add_cart_button->click();
    $this->waitForAjaxToFinish();

    /*
     * Confirm that the initial add to cart submit works.
     */
    $this->cart = Order::load($this->cart->id());
    $order_items = $this->cart->getItems();
    $this->assertOrderItemInOrder($this->variation, $order_items[0]);

    // Confirm that the cart block has been updated.
    $cart_block_contents = $this->xpath('//div[@class=:class]', [
      ':class' => 'cart-block--contents',
    ]);
    $this->assertEquals(1, count($cart_block_contents), 'Cart block not updated.');
  }

}
