<?php

namespace Drupal\Tests\commerce_cnpay\Kernel;

use Drupal\commerce_cnpay\AlipayClient;
use Drupal\commerce_payment\Entity\PaymentGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class AlipayTest extends CnpayTestBase {

  /**
   * The Alipay Page payment gateway.
   *
   * @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface
   */
  protected $pageGateway;

  /**
   * The Alipay Page payment gateway plugin.
   *
   * @var \Drupal\commerce_cnpay_test\Plugin\Commerce\PaymentGateway\AlipayTestPage
   */
  protected $pageGatewayPlugin;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Creates a WeChat Native payment gateway.
    $this->pageGateway = PaymentGateway::create($this->getGatewayConfiguration(TRUE));
    $this->pageGateway->save();
    $this->pageGatewayPlugin = $this->pageGateway->getPlugin();
  }

  /**
   * Tests refund() API.
   */
  public function testRefundApi() {
    $http_client = $this->getMockBuilder(Client::class)
      ->setMethods(['post'])
      ->getMock();
    $http_client->expects($this->once())
      ->method('post')
      ->willReturn(new Response(200, [], json_encode([
        'alipay_trade_refund_response' => [
          'code' => '10000',
          'msg' => 'Success',
          'buyer_logon_id' => '908***@qq.com',
          'buyer_user_id' => '2088101117955611',
          'fund_change' => 'Y',
          'gmt_refund_pay' => '2017-11-12 21:45:57',
          'out_trade_no' => '123',
          'refund_fee' => '1.0',
          'send_back_fee' => '0.00',
          'trade_no' => '2017123',
        ],
        'sign' => 'abcdefg123456789',
      ])));
    $config = $this->getGatewayConfiguration(TRUE)['configuration'];
    $client = new AlipayClient($config['app_id'], $config['app_private_key_path'], $config['alipay_public_key_path'], FALSE, $http_client);

    $result = $client->refund([
      'trade_no' => '2017123',
      'refund_amount' => '1.0',
      // 'refund_reason' => '',
      'out_request_no' => '123',
    ]);
    $this->assertEquals([
      'code' => '10000',
      'msg' => 'Success',
      'buyer_logon_id' => '908***@qq.com',
      'buyer_user_id' => '2088101117955611',
      'fund_change' => 'Y',
      'gmt_refund_pay' => '2017-11-12 21:45:57',
      'out_trade_no' => '123',
      'refund_fee' => '1.0',
      'send_back_fee' => '0.00',
      'trade_no' => '2017123',
    ], $result);
  }

  /**
   * Tests prepayPayment().
   */
  public function testPrepayPayment() {
    $payment = $this->createPayment();
    $prepay_response = $this->pageGatewayPlugin->prepayPayment($payment, [
      'return_url' => 'http://localhost/payment/return/test_alipay_page',
    ]);
    $this->assertEquals('http://localhost/payment/notify/test_alipay_page', $prepay_response['notify_url']);
    $this->assertEquals('http://localhost/payment/return/test_alipay_page', $prepay_response['return_url']);
  }

  /**
   * Gets payment gateway configuration for NATIVE or JSAPI.
   *
   * @param bool $page
   *   Whether the configuration is for Page pay.
   *
   * @return array
   *   The configuration array.
   */
  protected function getGatewayConfiguration($page) {
    $cert_folder = drupal_get_path('module', 'commerce_cnpay') . '/tests/cert';
    return [
      'id' => $page ? 'test_alipay_page' : 'test_alipay_wap',
      'plugin' => $page ? 'alipay_test_page' : 'alipay_test_wap',
      'configuration' => [
        'app_id' => '2017111111111111',
        'seller_id' => '2088123456789012',
        'app_private_key_path' => $cert_folder . '/apiclient_key.pem',
        'alipay_public_key_path' => $cert_folder . '/apiclient_cert.pem',
        'display_label' => 'Alipay',
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
   * @param bool $page
   *   Whether the configuration is for Page.
   *
   * @return \Drupal\commerce_payment\Entity\PaymentInterface
   */
  protected function createPayment($page = TRUE) {
    $payment = $this->paymentStorage->create([
      'state' => 'new',
      'amount' => $this->cart->getTotalPrice(),
      'payment_gateway' => $page ? 'test_alipay_page' : 'test_alipay_wap',
      'order_id' => $this->cart->id(),
    ]);
    return $payment;
  }

}
