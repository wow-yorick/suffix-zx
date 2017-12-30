<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

/**
 * Provides the WeChat Pay (Native) payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "wechat_pay_native",
 *   label = "WeChat Pay (Native)",
 *   display_label = "WeChat Pay",
 *   forms = {
 *     "offsite-payment" = "\Drupal\commerce_cnpay\PluginForm\WeChatPayNative\PaymentOffsiteForm",
 *   },
 *   trade_type = "NATIVE",
 * )
 */
class WeChatPayNative extends WeChatPaymentGatewayBase  {

}
