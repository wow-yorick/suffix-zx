<?php

namespace Drupal\Tests\dc_ajax_add_cart\FunctionalJavascript;

use Drupal\commerce_order\Entity\Order;
use Drupal\Tests\dc_ajax_add_cart\Functional\AjaxAddCartTestBase;

/**
 * Ajax add cart confirmation message tests.
 *
 * @TODO The default `testing` profile is unable to render the ajax confirmation
 * message. Find out why this is happening, and move this test inside
 * `AjaxAddCartTest`. If you are able to do this, add another test just like
 * `testAjaxAddCartForm()` that would test whether confirmation message is
 * indeed ajaxified.
 *
 * @ingroup dc_ajax_add_cart
 *
 * @group dc_ajax_add_cart
 */
class AjaxAddCartConfirmationMessageTest extends AjaxAddCartTestBase {

  /**
   * Profile to be used for testing.
   *
   * @var string
   */
  protected $profile = 'standard';

  /**
   * Tests whether the confirmation message appears after product added to cart.
   */
  public function testConfirmationMessage() {
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

    // Confirm that the confirmation message has appeared.
    $this->assertSession()->pageTextContains("{$this->variation->getProduct()->label()} added to your cart.", 'Confirmation message not found.');
  }

}
