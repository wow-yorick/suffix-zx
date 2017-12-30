<?php

namespace Drupal\commerce_cnpay_test\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;

trait TestGatewayTrait {

  /**
   * The id of a payment gateway the plugin API is set to.
   *
   * @var string
   */
  protected $paymentGatewayId;

  /**
   * The client for the reloaded payment instance.
   */
  protected $clientForReloadedPayment;

  /**
   * Sets a client for the reloaded payment.
   *
   * @param string $payment_gateway_id
   *   The payment gateway id.
   * @param mixed $client
   *   The client to injected into the reloaded payment instance.
   */
  public function setClientForReloadedPayment($payment_gateway_id, $client) {
    $this->paymentGatewayId = $payment_gateway_id;
    $this->clientForReloadedPayment = $client;
  }

  /**
   * {@inheritdoc}
   */
  protected function loadPendingPayment(OrderInterface $order) {
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = parent::loadPendingPayment($order);
    if ($payment && $payment->getPaymentGatewayId() == $this->paymentGatewayId) {
      $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
      $payment_gateway_plugin->setClient($this->clientForReloadedPayment);
    }
    return $payment;
  }

}
