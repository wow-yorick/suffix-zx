<?php

namespace Drupal\commerce_cnpay\Controller;

use Drupal\commerce_payment\Entity\PaymentGatewayInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentController {

  /**
   * Checks payment state.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentGatewayInterface $commerce_payment_gateway
   *   The payment gateway.
   * @param string $out_trade_no
   *   The out trade no.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   *
   * @todo: checkAccess: Ensure user authentication and order owner?
   */
  public function checkState(PaymentGatewayInterface $commerce_payment_gateway, $out_trade_no) {
    /** @var \Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway\CNPaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $commerce_payment_gateway->getPlugin();
    $payment = $payment_gateway_plugin->loadPaymentByOutTradeNo($out_trade_no);
    if ($payment && $payment->getState()->value === 'completed') {
      $data = [
        'success' => TRUE,
        'redirect' => Url::fromRoute('commerce_checkout.form', [
          'commerce_order' => $payment->getOrderId(),
          'step' => 'complete',
        ])->toString(),
      ];
    }
    else {
      // @todo: invoke queryPayment() api and sync the result to database?
      $data = [
        'success' => FALSE,
      ];
    }
    return new JsonResponse($data);
  }

}
