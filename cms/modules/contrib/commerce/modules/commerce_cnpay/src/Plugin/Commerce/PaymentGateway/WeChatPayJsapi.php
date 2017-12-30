<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;

/**
 * Provides the WeChat Pay (JSAPI) payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "wechat_pay_jsapi",
 *   label = "WeChat Pay (JSAPI)",
 *   display_label = "WeChat Pay",
 *   trade_type = "JSAPI",
 *   prepay_openid_provider = "wechat_mp",
 * )
 *
 * @todo: Do not show this payment gateway on PC web. see payment conditions
 */
class WeChatPayJsapi extends WeChatPaymentGatewayBase {

  /**
   * {@inheritdoc}
   */
  public function prepayPayment(PaymentInterface &$payment, array $params = NULL) {
    $prepay_response = parent::prepayPayment($payment, $params);
    if (empty($prepay_response['prepay_id'])) {
      throw new PaymentGatewayException($this->getResponseError($prepay_response));
    }
    return $this->getClient()->jssdk->bridgeConfig($prepay_response['prepay_id'], FALSE);
  }

}
