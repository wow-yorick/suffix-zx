<?php

namespace Drupal\commerce_cnpay\PluginForm;

use Drupal\commerce_payment\PluginForm\PaymentGatewayFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class PaymentQueryForm extends PaymentGatewayFormBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // $payment_gateway_plugin = $this->plugin;
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway\AlipayPaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $query_response = $payment_gateway_plugin->queryPayment($payment);

    $form['payment'] = [
      '#type' => 'table',
      '#caption' => $payment_gateway_plugin->getLabel(),
      '#header' => [$this->t('Name'), $this->t('Value')],
      '#process' => [
        [get_class($this), 'processActions'],
      ],
    ];
    foreach ($query_response as $name => $value) {
      $form['payment'][] = [
        'name' => [
          '#plain_text' => $name,
        ],
        'value' => [
          '#plain_text' => is_array($value) ? print_r($value, TRUE) : $value,
        ]
      ];
    }
    return $form;
  }

  // Hide the actions by default, they are not needed.
  public static function processActions(array $element, FormStateInterface $form_state, array &$complete_form) {
    $complete_form['actions']['#access'] = FALSE;
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Nothing.
  }

}
