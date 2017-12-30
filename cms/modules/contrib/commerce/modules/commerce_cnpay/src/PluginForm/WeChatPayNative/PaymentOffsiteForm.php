<?php

namespace Drupal\commerce_cnpay\PluginForm\WeChatPayNative;

use Com\Tecnick\Barcode\Barcode;
use Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway\CNPaymentGatewayBase;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

class PaymentOffsiteForm extends BasePaymentOffsiteForm {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // $payment_gateway_plugin = $this->plugin;
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway\WeChatPaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();

    $prepay_response = $payment_gateway_plugin->prepayPayment($payment);
    // If we didn't get a code_url back from WeChat, we need to exit checkout.
    if (empty($prepay_response['code_url'])) {
      throw new PaymentGatewayException($payment_gateway_plugin->getResponseError($prepay_response, CNPaymentGatewayBase::API_PREPAY));
    }

    $form['#attached']['library'][] = 'commerce_cnpay/check_state';
    $form['#attached']['drupalSettings']['polling'] = [
      'payment_gateway' => $payment->getPaymentGatewayId(),
      'out_trade_no' => $payment_gateway_plugin->getOutTradeNo($payment),
      'interval' => 3000,   // 3 seconds (in milliseconds)
      'timeout' => 600000,  // 10 minutes.
    ];

    $barcode = new Barcode();
    $code_obj = $barcode->getBarcodeObj(
      'QRCODE,H',              // barcode type and additional comma-separated parameters
      $prepay_response['code_url'], // data string to encode
      -8,                      // bar height (use absolute or negative value as multiplication factor)
      -8,                     // bar width (use absolute or negative value as multiplication factor)
      'black',                 // foreground color
      [-2, -2, -2, -2]              // padding (use absolute or negative values as multiplication factors)
    )->setBackgroundColor('white'); // background color

    // Output the barcode as HTML div (see other output formats in the documentation and examples)
    // $codeHtml = $code_obj->getHtmlDiv();
    $codePngData = base64_encode($code_obj->getPngData());
    $form['qrcode'] = [
      // '#markup' =>  Markup::create($qrMarkup) ,
      '#markup' => Markup::create('<img src="data:image/png;base64,' . $codePngData . '" alt="微信支付二维码" />'),
    ];

    return $form;
  }

}
