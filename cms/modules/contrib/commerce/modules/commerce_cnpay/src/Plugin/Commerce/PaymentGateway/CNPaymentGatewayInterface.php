<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsRefundsInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsVoidsInterface;
use Drupal\commerce_price\Price;

/**
 * Provides the interface for the payment gateway.
 */
interface CNPaymentGatewayInterface extends OffsitePaymentGatewayInterface, SupportsRefundsInterface, SupportsVoidsInterface {

  /**
   * Gets the openid provider for the prepay openid.
   *
   * Note: the provider refers to that come from open_connect module.
   *
   * @return String|boolean
   *   The open connect provider if the prepay needs an openid, FALSE otherwise.
   */
  public function getPrepayOpenidProvider();

  /**
   * Prepays a payment.
   *
   * Used by the offsite-payment plugin form or JSAPI.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment by reference which may point to an existing payment and its
   *   id will be used outside.
   * @param array $params
   *   (Optional) An array of prepay params.
   *
   * @return array
   *   An array of response data.
   */
  public function prepayPayment(PaymentInterface &$payment, array $params = NULL);

  /**
   * Queries a payment.
   *
   * Used by the offsite-payment plugin form or JSAPI.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment.
   *
   * @return array
   *   An array of response data.
   */
  public function queryPayment(PaymentInterface $payment);

  /**
   * Processes an incoming IPN request.
   *
   * @param array $ipn_data
   *   The IPN data.
   *
   * @return array
   *   A result array:
   *   - processed: whether the IPN is been successfully processed
   *   - message: internal verbose message
   */
  public function processIpn(array $ipn_data);

  /**
   * Verifies IPN.
   *
   * @param array $ipn_data
   *   The IPN data.
   * @param string $out_trade_no
   *   The payment out trade no.
   * @param \Drupal\commerce_price\Price $total_amount
   *   The payment total amount
   * @param bool $needs_verify_sign
   *   Whether to verify the IPN signature, defaults to TRUE.
   */
  public function verifyIpn(array $ipn_data, $out_trade_no, Price $total_amount, $needs_verify_sign = TRUE);

  /**
   * Whether the direct API call response is successful.
   *
   * @param array $response
   *   The response data.
   *
   * @return bool
   *   TRUE if the existing payment is available for reuse, FALSE otherwise.
   */
  public function isResponseSuccessful(array $response);

  /**
   * Gets the error message from the given response data.
   *
   * @param array $response
   *   The response data
   *
   * @return string
   *   The error message.
   */
  public function getResponseError(array $response);

  /**
   * Loads a payment for the given out trade no.
   *
   * @param string $out_trade_no
   *   The out trade no.
   *
   * @return \Drupal\commerce_payment\Entity\PaymentInterface|null
   *   A payment object, or NULL if no matching object is found.
   */
  public function loadPaymentByOutTradeNo($out_trade_no);

  /**
   * Loads an order for the given out trade no.
   *
   * @param string $out_trade_no
   *   The remote id property for a payment.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface|null
   *   An order object, or NULL if no matching object is found.
   */
  public function loadOrderByOutTradeNo($out_trade_no);

  /**
   * Gets an out trade no from the given payment.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment.
   *
   * @return string
   *   The out trade no.
   */
  public function getOutTradeNo(PaymentInterface $payment);

  /**
   * Gets an out refund no from the given payment.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment.
   *
   * @return string
   *   The out refund no.
   */
  public function getOutRefundNo(PaymentInterface $payment);

}
