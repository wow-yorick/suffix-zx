<?php

namespace Drupal\Tests\commerce_cnpay\Functional;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\commerce_price\Price;
use Drupal\Core\Url;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

class CnpayTest extends CommerceBrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'commerce_order',
    'commerce_payment',
    'commerce_cnpay',
  ];

  /**
   * The current user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $currency_importer = $this->container->get('commerce_price.currency_importer');
    $currency_importer->import('CNY');

    $gateway = PaymentGateway::create([
      'id' => 'test_alipay_wap',
      'plugin' => 'alipay_wap',
      'configuration' => [],
    ]);
    $gateway->save();

    $this->user = $this->createUser([
      'administer commerce_payment',
    ]);
    $this->drupalLogin($this->user);
  }

  /**
   * Tests query operation.
   */
  public function testQueryOperation() {
    $assert_session = $this->assertSession();

    $order = Order::create([
      'type' => 'default',
      'mail' => $this->user->getEmail(),
      'uid' => $this->user->id(),
      'store_id' => $this->store->id(),
    ]);
    $order->save();

    // Create a payment with the 'TRADE_SUCCESS' remote state.
    $payment = Payment::create([
      'state' => 'completed',
      'amount' => new Price('1.0', 'CNY'),
      'payment_gateway' => 'test_alipay_wap',
      'order_id' => $order->id(),
    ]);
    $payment->setRemoteState('TRADE_SUCCESS');
    $payment->save();

    // Ensure 'query' link exists.
    $this->drupalGet(Url::fromRoute('entity.commerce_payment.collection', [
      'commerce_order' => $order->id(),
    ]));
    $assert_session->statusCodeEquals(200);
    $assert_session->linkExists('Query');
    $assert_session->linkByHrefExists(Url::fromRoute('entity.commerce_payment.operation_form', [
      'commerce_order' => $order->id(),
      'commerce_payment' => $payment->id(),
      'operation' => 'query',
    ])->toString());
  }

  /**
   * Tests Alipay refund operation.
   */
  public function testAlipayRefundOperation() {
    $assert_session = $this->assertSession();

    $order = Order::create([
      'type' => 'default',
      'mail' => $this->user->getEmail(),
      'uid' => $this->user->id(),
      'store_id' => $this->store->id(),
    ]);
    $order->save();

    // Create a payment with the 'TRADE_SUCCESS' remote state.
    $payment = Payment::create([
      'state' => 'completed',
      'amount' => new Price('1.0', 'CNY'),
      'payment_gateway' => 'test_alipay_wap',
      'order_id' => $order->id(),
    ]);
    $payment->setRemoteState('TRADE_SUCCESS');
    $payment->save();

    // Ensure 'refund' link exists.
    $this->drupalGet(Url::fromRoute('entity.commerce_payment.collection', [
      'commerce_order' => $order->id(),
    ]));
    $assert_session->statusCodeEquals(200);
    $assert_session->linkExists('Refund');
    $assert_session->linkByHrefExists(Url::fromRoute('entity.commerce_payment.operation_form', [
      'commerce_order' => $order->id(),
      'commerce_payment' => $payment->id(),
      'operation' => 'refund',
    ])->toString());

    // Update the payment's remote state to 'TRADE_FINISHED'
    $payment->setRemoteState('TRADE_FINISHED');
    $payment->save();

    // Ensure 'refund' link is not found.
    $this->drupalGet(Url::fromRoute('entity.commerce_payment.collection', [
      'commerce_order' => $order->id(),
    ]));
    $assert_session->statusCodeEquals(200);
    $assert_session->linkNotExists('Refund');
  }

}
