<?php

namespace Drupal\commerce_cnpay_test\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway\AlipayPage;

/**
 * Provides the Alipay Test (Page) payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "alipay_test_page",
 *   label = "Alipay Test (Page)",
 *   display_label = "Alipay",
 *   forms = {
 *     "offsite-payment" = "\Drupal\commerce_cnpay\PluginForm\AlipayPage\PaymentOffsiteForm",
 *   },
 *   api = "alipay.trade.page.pay",
 *   product_code = "FAST_INSTANT_TRADE_PAY",
 * )
 */
class AlipayTestPage extends AlipayPage {

}
