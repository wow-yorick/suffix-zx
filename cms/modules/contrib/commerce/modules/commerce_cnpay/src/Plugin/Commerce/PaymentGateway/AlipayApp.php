<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

/**
 * Provides the Alipay (App) payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "alipay_app",
 *   label = "Alipay (App)",
 *   display_label = "Alipay",
 *   api = "alipay.trade.app.pay",
 *   product_code = "QUICK_MSECURITY_PAY",
 * )
 *
 * @todo: Do not show this payment gateway on PC web. see payment conditions
 */
class AlipayApp extends AlipayPaymentGatewayBase  {

}
