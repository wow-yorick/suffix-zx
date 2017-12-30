<?php

namespace Drupal\commerce_cnpay_test\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway\WeChatPayJsapi;

/**
 * Provides the WeChat Test JSAPI payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "wechat_test_jsapi",
 *   label = "WeChat Test (JSAPI) ",
 *   display_label = "WeChat Test JSAPI ",
 *   trade_type = "JSAPI",
 * )
 */
class WeChatTestJsapi extends WeChatPayJsapi {

  use TestGatewayTrait;

}
