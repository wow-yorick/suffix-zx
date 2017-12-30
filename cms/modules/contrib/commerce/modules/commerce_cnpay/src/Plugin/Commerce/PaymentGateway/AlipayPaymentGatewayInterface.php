<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_cnpay\AlipayClient;

/**
 * Provides the interface for the Alipay payment gateway.
 */
interface AlipayPaymentGatewayInterface extends CNPaymentGatewayInterface {

  /**
   * Gets the client to perform Alipay API calls.
   *
   * @return \Drupal\commerce_cnpay\AlipayClient
   *   The client.
   */
  public function getClient();

  /**
   * Sets the client to perform Alipay API calls.
   *
   * @param AlipayClient $client
   *   The client.
   */
  public function setClient(AlipayClient $client);

  /**
   * Gets the redirect URL.
   *
   * @return string
   *   The redirect URL.
   */
  public function getRedirectUrl();

}
