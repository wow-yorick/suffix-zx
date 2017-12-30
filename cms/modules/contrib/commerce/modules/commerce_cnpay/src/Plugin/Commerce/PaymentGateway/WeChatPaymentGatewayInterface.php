<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

use EasyWeChat\Payment\Application;

/**
 * Provides the interface for the WeChat payment gateway.
 */
interface WeChatPaymentGatewayInterface extends CNPaymentGatewayInterface {

  /**
   * Gets the client to perform WeChat Pay API calls.
   *
   * @return \EasyWeChat\Payment\Application
   *   The WeChat Pay client.
   */
  public function getClient();

  /**
   * Sets the client to perform WeChat Pay API calls.
   *
   * @param \EasyWeChat\Payment\Application $client
   *   The WeChat Pay client.
   */
  public function setClient(Application $client);

}
