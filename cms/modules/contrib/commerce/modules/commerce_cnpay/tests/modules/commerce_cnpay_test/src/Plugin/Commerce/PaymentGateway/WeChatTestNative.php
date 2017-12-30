<?php

namespace Drupal\commerce_cnpay_test\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway\WeChatPayNative;

/**
 * Provides the WeChat Test Native payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "wechat_test_native",
 *   label = "WeChat Test (Native)",
 *   display_label = "WeChat Test Native",
 *   forms = {
 *     "offsite-payment" = "\Drupal\commerce_cnpay\PluginForm\WeChatNative\PaymentOffsiteForm",
 *   },
 *   trade_type = "NATIVE",
 * )
 */
class WeChatTestNative extends WeChatPayNative  {

  use TestGatewayTrait;

}
