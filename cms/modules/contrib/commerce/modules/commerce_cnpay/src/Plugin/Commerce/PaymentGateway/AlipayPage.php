<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

/**
 * Provides the Alipay (Page) payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "alipay_page",
 *   label = "Alipay (Page)",
 *   display_label = "Alipay",
 *   forms = {
 *     "offsite-payment" = "\Drupal\commerce_cnpay\PluginForm\AlipayPage\PaymentOffsiteForm",
 *   },
 *   api = "alipay.trade.page.pay",
 *   product_code = "FAST_INSTANT_TRADE_PAY",
 * )
 */
class AlipayPage extends AlipayPaymentGatewayBase  {

}
