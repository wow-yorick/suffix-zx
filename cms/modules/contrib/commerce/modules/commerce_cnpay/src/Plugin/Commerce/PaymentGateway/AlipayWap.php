<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

/**
 * Provides the Alipay (Wap) payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "alipay_wap",
 *   label = "Alipay (Wap)",
 *   display_label = "Alipay",
 *   api = "alipay.trade.wap.pay",
 *   product_code = "QUICK_WAP_WAY",
 * )
 *
 * @todo: Do not show this payment gateway on PC web. see payment conditions
 */
class AlipayWap extends AlipayPaymentGatewayBase {

}
