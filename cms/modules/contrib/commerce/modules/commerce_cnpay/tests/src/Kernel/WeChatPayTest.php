<?php

namespace Drupal\Tests\commerce_cnpay\Kernel;

use Drupal\commerce_cnpay_test\WeChatTestClient;
use Drupal\commerce_cnpay_test\WeChatTestOrderClient;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_price\Price;
use EasyWeChat\Kernel\Support;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests WeChat payments.
 */
class WeChatPayTest extends CnpayTestBase {

  /**
   * The WeChat Native payment gateway.
   *
   * @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface
   */
  protected $nativeGateway;

  /**
   * The WeChat Native payment gateway plugin.
   *
   * @var \Drupal\commerce_cnpay_test\Plugin\Commerce\PaymentGateway\WeChatTestNative
   */
  protected $nativeGatewayPlugin;

  /**
   * The WeChat Jsapi payment gateway.
   *
   * @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface
   */
  protected $jsapiGateway;

  /**
   * The WeChat payment gateway plugin.
   *
   * @var \Drupal\commerce_cnpay_test\Plugin\Commerce\PaymentGateway\WeChatTestJsapi
   */
  protected $jsapiGatewayPlugin;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Creates a WeChat Native payment gateway.
    $this->nativeGateway = PaymentGateway::create($this->getGatewayConfiguration(TRUE));
    $this->nativeGateway->save();
    $this->nativeGatewayPlugin = $this->nativeGateway->getPlugin();

    // Creates a WeChat Jsapi payment gateway.
    $this->jsapiGateway = PaymentGateway::create($this->getGatewayConfiguration(FALSE));
    $this->jsapiGateway->save();
    $this->jsapiGatewayPlugin = $this->jsapiGateway->getPlugin();
  }

  /**
   * Tests prepayPayment() in normal flow.
   */
  public function testPrepayPaymentNormal() {
    $client = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client->expects($this->exactly(2))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->returnValue($this->createSuccessPrepayResponse()));
    $client->expects($this->never())
      ->method('close');
    $this->nativeGatewayPlugin->setClient(new WeChatTestClient($client));

    // Assert $payment1's initial state.
    $payment1 = $this->createPayment();
    $this->assertTrue($payment1->isNew());
    $this->assertEquals('new', $payment1->getState()->value);
    $this->assertEquals('', $payment1->getRemoteId());
    $this->assertEquals('', $payment1->getRemoteState());

    // Prepay payment1.
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment1);
    $this->assertEquals('wx20170901221949114232', $prepay_response['prepay_id']);

    // Assert the prepayed payment1.
    $this->assertFalse($payment1->isNew(), 'Payment1 is saved.');
    $this->assertEquals('authorization', $payment1->getState()->value);
    $this->assertEquals('', $payment1->getRemoteId());
    $this->assertEquals('NOTPAY', $payment1->getRemoteState());

    // Prepay payment2 for the same order.
    $payment2 = $this->createPayment();
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment2);
    $this->assertEquals('wx20170901221949114232', $prepay_response['prepay_id']);

    // Assert payment2.
    $this->assertEquals($payment1->id(), $payment2->id(), 'Payment2 points to payment1 and voidPayment() is not called.');
    $this->assertEquals('authorization', $payment2->getState()->value);
    $this->assertEquals('', $payment2->getRemoteId());
    $this->assertEquals('NOTPAY', $payment2->getRemoteState());

    // Assert payment1 is the only payment for the order.
    $payments =  $this->paymentStorage->loadMultipleByOrder($this->cart);
    $this->assertEquals(1, count($payments));
    $payment = reset($payments);
    $this->assertEquals($payment1->id(), $payment->id(), 'Payment1 is the only payment.');
  }

  /**
   * Tests prepayPayment() with amount change.
   */
  public function testPrepayPaymentWithAmountChange() {
    $client = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client->expects($this->exactly(2))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->returnValue($this->createSuccessPrepayResponse()));
    $client->expects($this->exactly(1))
      ->method('close')
      ->with($this->stringStartsWith($this->cart->id()))
      ->will($this->returnValue($this->createSuccessVoidResponse()));
    $this->nativeGatewayPlugin->setClient(new WeChatTestClient($client));

    // Prepay payment1.
    $payment1 = $this->createPayment();
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment1);
    $this->assertEquals('wx20170901221949114232', $prepay_response['prepay_id']);
    $this->assertFalse($payment1->isNew(), 'Payment1 is saved.');

    // Prepay payment2 with changed amount for same order.
    $payment2 = $this->createPayment();
    $payment2->setAmount($payment1->getAmount()->multiply(10));
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment2);
    $this->assertEquals('wx20170901221949114232', $prepay_response['prepay_id']);

    // Assert payment2.
    $this->assertFalse($payment2->isNew(), 'Payment2 is saved instead of payment1 and voidPayment() is called.');
    $this->assertEquals('authorization', $payment2->getState()->value);
    $this->assertEquals('', $payment2->getRemoteId());
    $this->assertEquals('NOTPAY', $payment2->getRemoteState());
    $this->assertNull($this->paymentStorage->load($payment1->id()),'Payment1 is deleted.');

    // Assert payment2 is the only payment for the order.
    $payments =  $this->paymentStorage->loadMultipleByOrder($this->cart);
    $this->assertEquals(1, count($payments));
    $payment = reset($payments);
    $this->assertEquals($payment2->id(), $payment->id(), 'Payment2 is the only payment.');
  }

  /**
   * Tests prepayPayment() with gateway change.
   */
  public function testPrepayPaymentWithGatewayChange() {
    $client1 = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client1->expects($this->exactly(1))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->returnValue($this->createSuccessPrepayResponse()));
    $client1->expects($this->never())
      ->method('close');
    $this->nativeGatewayPlugin->setClient(new WeChatTestClient($client1));

    $client2 = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client2->expects($this->exactly(1))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->returnValue($this->createSuccessPrepayResponse()));
    $client2->expects($this->never())
      ->method('close');
    $this->jsapiGatewayPlugin->setClient(new WeChatTestClient($client2));

    $new_native_client = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $new_native_client->expects($this->never())
      ->method('unify');
    $new_native_client->expects($this->exactly(1))
      ->method('close')
      ->with($this->stringStartsWith($this->cart->id()))
      ->will($this->returnValue($this->createSuccessVoidResponse()));

    // Set the mocked client to the new payment instance of payment1 which is
    // created by $this->jsapiGatewayPlugin->loadPendingPayment() when prepaying
    // payment2. The plugin of test_wechat_native gateway of the new payment
    // will do: $new_native_gateway_plugin->doVoidPayment().
    $this->jsapiGatewayPlugin->setClientForReloadedPayment('test_wechat_native', new WeChatTestClient($new_native_client));

    // Prepay payment1.
    $payment1 = $this->createPayment();
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment1);
    $this->assertEquals('wx20170901221949114232', $prepay_response['prepay_id']);
    $this->assertFalse($payment1->isNew(), 'Payment1 is saved.');

    // Prepay payment2 with changed gateway for same order.
    $payment2 = $this->createPayment(FALSE);
    $prepay_response = $this->jsapiGatewayPlugin->prepayPayment($payment2);
    // JSAPI returns processed parameter array for prepay.
    $this->assertEquals('prepay_id=wx20170901221949114232', $prepay_response['package']);

    // Assert payment2.
    $this->assertFalse($payment2->isNew(), 'Payment2 is saved instead of payment1 and native voidPayment() is called.');
    $this->assertEquals('authorization', $payment2->getState()->value);
    $this->assertEquals('', $payment2->getRemoteId());
    $this->assertEquals('NOTPAY', $payment2->getRemoteState());
    $this->assertNull($this->paymentStorage->load($payment1->id()),'Payment1 is deleted.');

    // Assert payment2 is the only payment for the order.
    $payments =  $this->paymentStorage->loadMultipleByOrder($this->cart);
    $this->assertEquals(1, count($payments));
    $payment = reset($payments);
    $this->assertEquals($payment2->id(), $payment->id(), 'Payment2 is the only payment.');
  }

  /**
   * Tests prepayPayment() with failure response first time.
   */
  public function testPrepayPaymentWithPrepayFailureFirstTime() {
    $prepay_response = [
      'return_code'  => 'FAIL',
      'return_msg'  => 'appid xxx',
    ];
    $client = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client->expects($this->exactly(1))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->returnValue($prepay_response));
    $client->expects($this->never())
      ->method('close');
    $this->nativeGatewayPlugin->setClient(new WeChatTestClient($client));

    // Prepay payment1.
    $payment1 = $this->createPayment();
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment1);
    $this->assertEquals('FAIL', $prepay_response['return_code']);
    $this->assertNull($this->paymentStorage->load($payment1->id()), 'Payment1 is deleted and voidPayment() is not called..');
    $this->assertEmpty($this->paymentStorage->loadMultipleByOrder($this->cart), 'No payments for the order are found.');
  }

  /**
   * Tests prepayPayment() with failure response second time.
   */
  public function testPrepayPaymentWithPrepayFailureSecondTime() {
    $this->success_response = TRUE;

    $return_callback = function () {
      if ($this->success_response) {
        return $this->createSuccessPrepayResponse();
      }
      return [
        'return_code'  => 'SUCCESS',
        'result_code'  => 'FAIL',
        'err_code'     => 'INVALID_REQUEST',
        'err_code_des' => '201 Duplicate out trade no',
      ];
    };
    $client = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client->expects($this->exactly(2))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->returnCallback($return_callback));
    $client->expects($this->exactly(1))
      ->method('close')
      ->with($this->stringStartsWith($this->cart->id()))
      ->will($this->returnValue($this->createSuccessVoidResponse()));
    $this->nativeGatewayPlugin->setClient(new WeChatTestClient($client));

    // Prepay payment1.
    $payment1 = $this->createPayment();
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment1);
    $this->assertEquals('wx20170901221949114232', $prepay_response['prepay_id']);
    $this->assertFalse($payment1->isNew(), 'Payment1 is saved.');
    $this->assertEquals('authorization', $payment1->getState()->value);

    $this->success_response = FALSE;

    // Prepay payment2 for the same order.
    $payment2 = $this->createPayment();
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment2);
    $this->assertEquals('INVALID_REQUEST', $prepay_response['err_code']);
    $this->assertEquals($payment1->id(), $payment2->id(), 'Payment2 points to payment1.');
    $this->assertNull($this->paymentStorage->load($payment1->id()), 'Payment1 is deleted and voidPayment() is called.');
    $this->assertEmpty($this->paymentStorage->loadMultipleByOrder($this->cart), 'No payments for the order are found.');
  }

  /**
   * Test prepayPayment() with doPrepayPayment() exception first time.
   */
  public function testPrepayPaymentWithDoPrepayPaymentExceptionFirstTime() {
    $request = new \GuzzleHttp\Psr7\Request('POST', 'https://api.mch.weixin.qq.com/pay/unifiedorder');
    $request_exception = new RequestException('cURL error 28: Resolving timed out after 5514 milliseconds', $request);

    $client = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client->expects($this->exactly(1))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->throwException($request_exception));
    $client->expects($this->never())
      ->method('close');
    $this->nativeGatewayPlugin->setClient(new WeChatTestClient($client));

    // Prepay payment1.
    $payment1 = $this->createPayment();
    try {
      $this->nativeGatewayPlugin->prepayPayment($payment1);
      $this->fail('PaymentGatewayException should be thrown if prepare() throws a RequestException.');
    }
    catch (PaymentGatewayException $e) {
      $this->assertEquals($this->nativeGatewayPlugin->getLabel() . ' Could not prepay payment: cURL error 28: Resolving timed out after 5514 milliseconds', $e->getMessage());
    }
    $this->assertNull($this->paymentStorage->load($payment1->id()), 'Payment1 is deleted and voidPayment() is not called..');
    $this->assertEmpty($this->paymentStorage->loadMultipleByOrder($this->cart), 'No payments for the order are found.');
  }

  /**
   * Test prepayPayment() with doPrepayPayment() exception second time.
   */
  public function testPrepayPaymentWithDoPrepayPaymentExceptionSecondTime() {
    $this->success_response = TRUE;

    $return_callback = function () {
      if ($this->success_response) {
        return $this->createSuccessPrepayResponse();
      }
      $request = new \GuzzleHttp\Psr7\Request('POST', 'https://api.mch.weixin.qq.com/pay/unifiedorder');
      throw new RequestException('cURL error 28: Resolving timed out after 5514 milliseconds', $request);
    };

    $client = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client->expects($this->exactly(2))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->returnCallback($return_callback));
    $client->expects($this->never())
      ->method('close');
    $this->nativeGatewayPlugin->setClient(new WeChatTestClient($client));

    // Prepay payment1.
    $payment1 = $this->createPayment();
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment1);
    $this->assertEquals('wx20170901221949114232', $prepay_response['prepay_id']);
    $this->assertFalse($payment1->isNew(), 'Payment1 is saved.');
    $this->assertEquals('authorization', $payment1->getState()->value);

    $this->success_response = FALSE;

    // Prepay payment2 for the same order.
    $payment2 = $this->createPayment();
    try {
      $this->nativeGatewayPlugin->prepayPayment($payment2);
      $this->fail('PaymentGatewayException should be thrown if prepare() throws a RequestException.');
    }
    catch (PaymentGatewayException $e) {
      $this->assertEquals($this->nativeGatewayPlugin->getLabel() . ' Could not prepay payment: cURL error 28: Resolving timed out after 5514 milliseconds', $e->getMessage());
    }

    // Assert payment2.
    $this->assertEquals($payment1->id(), $payment2->id(), 'Payment2 points to payment1 and voidPayment() is not called.');

    // Assert payment1.
    $payments = $this->paymentStorage->loadMultipleByOrder($this->cart);
    $this->assertEquals(1, count($payments));
    $this->assertEquals($payment1->id(), reset($payments)->id(), 'Payment1 is still valid.');
    $this->assertEquals('authorization', $payment1->getState()->value);
    $this->assertEquals('', $payment1->getRemoteId());
    $this->assertEquals('NOTPAY', $payment1->getRemoteState());
  }

  /**
   * Test prepayPayment() with doVoidPayment() exception.
   *
   * Ensure the existing payment is deleted when voiding it even an exception
   * is thrown during voiding.
   */
  public function testPrepayPaymentWithDoVoidPaymentException() {
    $exception = new \InvalidArgumentException('json_decode error: Malformed UTF-8 characters, possibly incorrectly encoded in GuzzleHttp\json_decode()');

    $client = $this->getMockBuilder(WeChatTestOrderClient::class)
      ->setMethods(['unify', 'close'])
      ->getMock();
    $client->expects($this->exactly(1))
      ->method('unify')
      ->with($this->isType('array'))
      ->will($this->returnValue($this->createSuccessPrepayResponse()));
    $client->expects($this->exactly(1))
      ->method('close')
      ->with($this->stringStartsWith($this->cart->id()))
      ->will($this->throwException($exception));
    $this->nativeGatewayPlugin->setClient(new WeChatTestClient($client));

    // Prepay payment1.
    $payment1 = $this->createPayment();
    $prepay_response = $this->nativeGatewayPlugin->prepayPayment($payment1);
    $this->assertEquals('wx20170901221949114232', $prepay_response['prepay_id']);
    $this->assertFalse($payment1->isNew(), 'Payment1 is saved.');
    $this->assertEquals('authorization', $payment1->getState()->value);

    // Prepay payment2 with changed amount for same order, which will cause
    // the existing payment to be voided.
    $payment2 = $this->createPayment();
    $payment2->setAmount($payment1->getAmount()->multiply(10));
    try {
      $this->nativeGatewayPlugin->prepayPayment($payment2);
      $this->fail('InvalidArgumentException should be thrown by close().');
    }
    catch (\InvalidArgumentException $e) {
      $this->assertEquals('json_decode error: Malformed UTF-8 characters, possibly incorrectly encoded in GuzzleHttp\json_decode()', $e->getMessage());
    }

    $this->assertTrue($payment2->isNew(), 'Payment2 is not saved yet.');
    $this->assertNull($this->paymentStorage->load($payment1->id()), 'Payment1 is deleted anyway even an exception is thrown.');
    $this->assertEmpty($this->paymentStorage->loadMultipleByOrder($this->cart), 'No payments for the order are found.');
  }

  /**
   * Tests prepayPayment() with a placed order.
   *
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage The provided order is in an invalid state ("completed").
   */
  public function testPrepayPaymentWithPlacedOrder() {
    // Prepay payment.
    $payment = $this->createPayment();
    $order = $payment->getOrder();
    $order->set('checkout_step', 'complete');
    $transition = $order->getState()->getWorkflow()->getTransition('place');
    $order->getState()->applyTransition($transition);
    $order->save();
    $this->nativeGatewayPlugin->prepayPayment($payment);
  }

  /**
   * Tests processIpn() with success IPN data (result_code='SUCCESS').
   */
  public function testProcessIpnWithSuccessData() {
    $payment = $this->createPayment();
    $ipn_data = $this->createIpnData($this->cart, $payment);
    $result = $this->nativeGatewayPlugin->processIpn($ipn_data);
    $this->assertEquals([
      'processed' => TRUE,
      'message' => 'OK: IPN processed',
    ], $result);

    // Assert the order.
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->reloadEntity($this->cart);
    $this->assertEquals('complete', $order->get('checkout_step')->value);
    $this->assertEquals('completed', $order->getState()->value);

    // Assert the payment.
    $payments =  $this->paymentStorage->loadMultipleByOrder($order);
    $this->assertEquals(1, count($payments));
    $this->assertEquals($payment->id(), reset($payments)->id());
    $payment = $this->reloadEntity($payment);
    // @todo:
    // 1. Set checkout flow to 'custom_checkout' and  assert order state='fulfillment'.
    // It's 'completed' because order's checkout flow is 'default'.
    $this->assertEquals('completed', $payment->getState()->value);
    $this->assertEquals(new Price('1.00', 'CNY'), $payment->getAmount());
    $this->assertEquals('test_wechat_native', $payment->getPaymentGatewayId());
    $this->assertEquals('4010002001201709019477973290', $payment->getRemoteId());
    $this->assertEquals('SUCCESS', $payment->getRemoteState());

    // Assert no carts.
    $carts = $this->cartProvider->getCarts();
    $this->assertEmpty($carts);

    // Process same incoming IPN again.
    $result = $this->nativeGatewayPlugin->processIpn($ipn_data);
    $this->assertEquals([
      'processed' => TRUE,
      'message' => 'Ignored: IPN already processed',
    ], $result);

    // Assert no new payments are created.
    $payments = $this->paymentStorage->loadMultipleByOrder($order);
    $this->assertEquals(1, count($payments));
    $this->assertEquals($payment->id(), reset($payments)->id());
  }

  /**
   * Tests processIpn() with failure IPN data.
   */
  public function testProcessIpnWithFailureData() {
    // Failure IPN with return_code of 'FAIL'.
    $ipn_data = [
      'return_code' => 'FAIL',
      'return_msg' => 'some error',
    ];
    $result = $this->nativeGatewayPlugin->processIpn($ipn_data);
    $this->assertEquals([
      'processed' => TRUE,
      'message' => 'Ignored: failure IPN received',
    ], $result);

    // Failure IPN with result_code of 'FAIL'.
    $payment = $this->createPayment();
    $ipn_data = $this->createIpnData($this->cart, $payment, [
      'result_code' => 'FAIL',
    ]);
    $result = $this->nativeGatewayPlugin->processIpn($ipn_data);
    $this->assertEquals([
      'processed' => TRUE,
      'message' => 'OK: IPN processed',
    ], $result);

    // Assert the order.
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->reloadEntity($this->cart);
    $this->assertEquals('payment', $order->get('checkout_step')->value);
    $this->assertEquals('draft', $order->getState()->value);

    // Assert the payment.
    $payment = $this->reloadEntity($payment);
    $this->assertEquals('authorization', $payment->getState()->value, 'Payment state does not change.');
    $this->assertEquals(new Price('1.00', 'CNY'), $payment->getAmount());
    $this->assertEquals('test_wechat_native', $payment->getPaymentGatewayId());
    $this->assertEquals('4010002001201709019477973290', $payment->getRemoteId());
    $this->assertEquals('PAYERROR', $payment->getRemoteState(), 'Payment remote state updated.');
  }

  /**
   * Tests processIpn() with failure IPN data then success IPN data.
   */
  public function testProcessIpnWithFailureThenSuccessData() {
    // Failure IPN with result_code of 'FAIL'.
    $payment = $this->createPayment();
    $ipn_data = $this->createIpnData($this->cart, $payment, [
      'result_code' => 'FAIL',
    ]);
    $result = $this->nativeGatewayPlugin->processIpn($ipn_data);
    $this->assertEquals([
      'processed' => TRUE,
      'message' => 'OK: IPN processed',
    ], $result);

    // Assert the payment.
    $payment = $this->reloadEntity($payment);
    $this->assertEquals('authorization', $payment->getState()->value);
    $this->assertEquals('PAYERROR', $payment->getRemoteState());

    // Success IPN with result_code of 'SUCCESS'.
    $ipn_data = $this->createIpnData($this->cart, $payment);
    $result = $this->nativeGatewayPlugin->processIpn($ipn_data);
    $this->assertEquals([
      'processed' => TRUE,
      'message' => 'OK: IPN processed',
    ], $result);

    // Assert the payment.
    $payment = $this->reloadEntity($payment);
    $this->assertEquals('completed', $payment->getState()->value, 'Payment state updated.');
    $this->assertEquals('SUCCESS', $payment->getRemoteState(), 'Payment remote state updated.');
  }

  /**
   * Tests payment final amount.
   */
  public function testFinalAmount() {
    $payment = $this->createPayment();
    $ipn_data = $this->createIpnData($this->cart, $payment, [
      'cash_fee' => '88',
    ]);
    $result = $this->nativeGatewayPlugin->processIpn($ipn_data);
    $this->assertEquals([
      'processed' => TRUE,
      'message' => 'OK: IPN processed',
    ], $result);

    // Assert the payment.
    $payment = $this->reloadEntity($payment);
    $this->assertEquals(new Price('0.88', 'CNY'), $payment->getAmount(), 'Final amount is set.');
    $this->assertEquals('completed', $payment->getState()->value);
    $this->assertEquals('SUCCESS', $payment->getRemoteState());
  }

  /**
   * Tests processIpn() with IPN data that is lost in our system.
   */
  public function testProcessIpnWithLostData() {
    $non_existent_out_trade_no = '1_9999';
    $payment = $this->createPayment();
    $ipn_data = $this->createIpnData($this->cart, $payment);
    $ipn_data['out_trade_no'] = $non_existent_out_trade_no;

    // Process incoming IPN with non-existent out trade no.
    $result = $this->nativeGatewayPlugin->processIpn($ipn_data);
    $this->assertEquals([
      'processed' => TRUE,
      'message' => 'Ignored: could not load the payment',
    ], $result);
  }

  /**
   * Tests onNotify() with invalid signature.
   */
  public function testOnNotifyWithInvalidSignature() {
    $url = $this->nativeGatewayPlugin->getNotifyUrl()->toString();
    $payment = $this->createPayment();
    $content = $this->createIpnData($this->cart, $payment);
    $content['sign'] = 'invalid_signature';
    $content = Support\XML::build($content);
    $response = $this->httpKernel->handle(Request::create($url, 'POST', [], [], [], [], $content));
    $data = Support\XML::parse($response->getContent());
    $this->assertEquals([
      'return_code' => 'FAIL',
      'return_msg' => 'Invalid signature',
    ], $data);
  }

  /**
   * Tests onNotify() success.
   */
  public function testOnNotifySuccess() {
    $url = $this->nativeGatewayPlugin->getNotifyUrl()->toString();
    $payment = $this->createPayment();
    $content = Support\XML::build($this->createIpnData($this->cart, $payment));
    $response = $this->httpKernel->handle(Request::create($url, 'POST', [], [], [], [], $content));
    $data = Support\XML::parse($response->getContent());
    $this->assertEquals([
      'return_code' => 'SUCCESS',
      'return_msg' => '',
    ], $data);
  }

  /**
   * Gets payment gateway configuration for NATIVE or JSAPI.
   *
   * @param bool $native
   *   Whether the configuration is for NATIVE.
   *
   * @return array
   *   The configuration array.
   */
  protected function getGatewayConfiguration($native) {
    $cert_folder = drupal_get_path('module', 'commerce_cnpay') . '/tests/cert';
    return [
      'id' => $native ? 'test_wechat_native' : 'test_wechat_jsapi',
      'plugin' => $native ? 'wechat_test_native' : 'wechat_test_jsapi',
      'configuration' => [
        'app_id' => 'wxc123456789012345',
        'mch_id' => '1234567890',
        'key' => 'ABCDEFG123456789abcdefg123456789',
        'cert_path' => $cert_folder . '/apiclient_cert.pem',
        'key_path' => $cert_folder . '/apiclient_key.pem',
        'display_label' => 'WeChat',
        'mode' => 'live',
        'payment_method_types' => [
          'credit_card',
        ],
      ],
    ];
  }

  /**
   * Creates a payment.
   *
   * Note: do not save the payment.
   *
   * @param bool $native
   *   Whether the configuration is for NATIVE.
   *
   * @return \Drupal\commerce_payment\Entity\PaymentInterface
   */
  protected function createPayment($native = TRUE) {
    $payment = $this->paymentStorage->create([
      'state' => 'new',
      'amount' => $this->cart->getTotalPrice(),
      'payment_gateway' => $native ? 'test_wechat_native' : 'test_wechat_jsapi',
      'order_id' => $this->cart->id(),
    ]);
    return $payment;
  }

  /**
   * Creates successful (result_code='SUCCESS') prepay data.
   */
  protected function createSuccessPrepayResponse() {
    $data = [
      'return_code' => 'SUCCESS',
      'return_msg' => 'OK',
      'appid' => 'wxc123456789012345',
      'mch_id' => '1234567890',
      'nonce_str' => '59a96c85a054c',
      'sign' => '',
      'result_code' => 'SUCCESS',
      'prepay_id' => 'wx20170901221949114232',
      'trade_type' => 'NATIVE',
      'code_url' => 'weixin://wxpay/bizpayurl?pr=1234567'
    ];

    // Signs the data.
    $sign_data = $data;
    unset($sign_data['sign']);
    $sign = Support\generate_sign($sign_data, 'ABCDEFG123456789abcdefg123456789', 'md5');

    $data['sign'] = $sign;
    return $data;
  }

  /**
   * Creates IPN data.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $cart
   *   The cart order.
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment object.
   * @param array $data_overrides
   *   (Optional) an array of data overrides.
   *
   * @return array
   *   The IPN array data.
   *
   * @see \EasyWeChat\Payment\Notify::isValid()
   *
   * @todo: Make sure the failure IPN data shape.
   *
   * We don't know the shape of the failure data because there is no examples
   * for failure case, and for example refund failure response does not include
   * the 'transaction_id' or 'out_trade_no':
   * [
   *   'return_code' => 'SUCCESS',
   *   'return_msg' => 'OK',
   *   'appid' => 'wxc123456789012345',
   *   'mch_id' => '1234567890',
   *   'nonce_str' => '59a8d3009ee21',
   *   'sign' => '',
   *   'result_code' => 'FAIL',
   *   'err_code' => 'ERROR',
   *   'err_code_des' => 'error message',
   * ]
   *
   * @throws
   */
  protected function createIpnData(OrderInterface $cart, PaymentInterface $payment, array $data_overrides = []) {
    if ($cart->cart->value === TRUE) {
      $cart
        ->set('checkout_step', 'payment')
        ->save();
    }
    if ($payment->isNew()) {
      $payment
        ->setState('authorization')
        ->setRemoteState('NOTPAY')
        ->save();
    }
    $total_fee = (int) $cart->getTotalPrice()->multiply('100')->getNumber();
    $out_trade_no = $this->nativeGatewayPlugin->getOutTradeNo($payment);
    $data = [
      'appid' => 'wxc123456789012345',
      'bank_type' => 'CFT',
      'cash_fee' => $total_fee,
      'fee_type' => 'CNY',
      'is_subscribe' => 'Y',
      'mch_id' => '1234567890',
      'nonce_str' => '59a8d3009ee21',
      'openid' => 'oaP8txLO02_86TORjDgtDuwjDwKI',
      'out_trade_no' => $out_trade_no,
      'result_code' => 'SUCCESS',
      'return_code' => 'SUCCESS',
      'sign' => '',
      'time_end' => '20170901112948',
      'total_fee' => $total_fee,
      'trade_type' => 'NATIVE',
      'transaction_id' => '4010002001201709019477973290',
    ];
    if ($data_overrides) {
      $data = array_merge($data, $data_overrides);
    }

    // Signs the data.
    $sign_data = $data;
    unset($sign_data['sign']);
    $sign = Support\generate_sign($sign_data, 'ABCDEFG123456789abcdefg123456789', 'md5');

    $data['sign'] = $sign;
    return $data;
  }

  /**
   * Creates successful (result_code='SUCCESS') void data.
   */
  protected function createSuccessVoidResponse() {
    $data = [
      'return_code' => 'SUCCESS',
      'return_msg' => 'OK',
      'appid' => 'wxc123456789012345',
      'mch_id' => '1234567890',
      'nonce_str' => '59a8d3009ee21',
      'sign' => '',
      'result_code' => 'SUCCESS',
    ];

    // Signs the data.
    $sign_data = $data;
    unset($sign_data['sign']);
    $sign = Support\generate_sign($sign_data, 'ABCDEFG123456789abcdefg123456789', 'md5');

    $data['sign'] = $sign;
    return $data;
  }

}
