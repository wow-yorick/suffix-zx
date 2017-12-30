<?php

namespace Drupal\commerce_cnpay\PluginForm\AlipayPage;

use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;

class PaymentOffsiteForm extends BasePaymentOffsiteForm {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // $payment_gateway_plugin = $this->plugin;
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway\AlipayPaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();

    $params = [
      'return_url' => $form['#return_url'],
      // 'cancel_url' => $form['#cancel_url'],
    ];
    $prepay_response = $payment_gateway_plugin->prepayPayment($payment, $params);
    // If we didn't get a sign back, then the we need to exit checkout.
    if (empty($prepay_response['sign'])) {
      throw new PaymentGatewayException(sprintf('%s prepay payment, %s',
        $payment_gateway_plugin->getLabel(),
        'sign failed'
      ));
    }

    // Note: If the form method is 'get', no redirect form is shown and a 302
    // location is sent directly, but because Alipay has much more parameters we
    // use the post method.
    return $this->buildRedirectForm($form, $form_state, $payment_gateway_plugin->getRedirectUrl(), $prepay_response, self::REDIRECT_POST);
  }

  /**
   * {@inheritdoc}
   *
   * @todo: Hide the submit button and remove the commerce message, just make a
   * blank page like Alipay PHP demo does (AopClient::buildRequestForm()).
   */
  public static function processRedirectForm(array $element, FormStateInterface $form_state, array &$complete_form) {
    // Remove 'form_id', 'form_build_id' and 'form_token'.
    unset($complete_form['form_id'], $complete_form['form_build_id'], $complete_form['form_token']);
    // Remove the 'name' attribute from submit button.
    unset($complete_form['actions']['next']['#name']);
    return parent::processRedirectForm($element, $form_state, $complete_form);
  }

}
