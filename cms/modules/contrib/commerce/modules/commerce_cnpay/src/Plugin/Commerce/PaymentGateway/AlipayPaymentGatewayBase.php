<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_cnpay\AlipayClient;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlipayPaymentGatewayBase extends CNPaymentGatewayBase implements AlipayPaymentGatewayInterface {

  /**
   * The price rounder.
   *
   * @var \Drupal\commerce_price\RounderInterface
   */
  protected $rounder;

  /**
   * The Alipay client.
   *
   * @var AlipayClient
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    PaymentTypeManager $payment_type_manager,
    PaymentMethodTypeManager $payment_method_type_manager,
    TimeInterface $time,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
    RounderInterface $rounder
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_type_manager,
      $payment_type_manager,
      $payment_method_type_manager,
      $time,
      $config_factory,
      $logger_factory
    );

    $this->rounder = $rounder;

    if (empty($plugin_definition['api'])) {
      throw new InvalidPluginDefinitionException($plugin_id, 'Plugin api is not defined.');
    }
    if (empty($plugin_definition['product_code'])) {
      throw new InvalidPluginDefinitionException($plugin_id, 'Plugin product_code is not defined.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('datetime.time'),
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('commerce_price.rounder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'dev_notify_domain' => '',
      'app_id' => '',
      'seller_id' => '',
      'app_private_key_path' => '',
      'alipay_public_key_path' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Development configuration.
    // $this->entityId is not available when building this plugin configuration
    // form since the plugin is create via plugin manager directly rather than
    // PaymentGateway, see PluginConfiguration::processPluginConfiguration().
    $mode_parents = array_merge($form['#parents'], ['mode']);
    $mode_path = array_shift($mode_parents);
    $mode_path .= '[' . implode('][', $mode_parents) . ']';
    $form['dev_notify_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Development notify domain'),
      '#default_value' => $this->configuration['dev_notify_domain'],
      '#description' => $this->t('Do <strong>NOT</strong> begin with http:// or https://.'),
      '#required' => FALSE,
      '#states' => [
        'invisible' => [
          ':input[name="' . $mode_path . '"]' => ['value' => 'live'],
        ],
      ],
    ];

    // Pay configurations.
    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $this->configuration['app_id'],
      '#required' => TRUE,
    ];
    $form['seller_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Seller ID'),
      '#default_value' => $this->configuration['seller_id'],
      '#required' => TRUE,
    ];
    $form['app_private_key_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App private key path'),
      '#default_value' => $this->configuration['app_private_key_path'],
      '#required' => TRUE,
    ];
    $form['alipay_public_key_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alipay public key path'),
      '#default_value' => $this->configuration['alipay_public_key_path'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    if ($values['mode'] !== 'live' && empty($values['dev_notify_domain'])) {
      $form_state->setError($form['dev_notify_domain'], 'The domain can be empty.');
    }
    if (!file_exists($values['app_private_key_path'])) {
      $form_state->setError($form['app_private_key_path'], 'The App private key file at given path does not exist.');
    }
    if (!file_exists($values['alipay_public_key_path'])) {
      $form_state->setError($form['alipay_public_key_path'], 'The Alipay public file at given path does not exist.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['dev_notify_domain'] = $values['dev_notify_domain'];
      $this->configuration['app_id'] = $values['app_id'];
      $this->configuration['seller_id'] = $values['seller_id'];
      $this->configuration['app_private_key_path'] = $values['app_private_key_path'];
      $this->configuration['alipay_public_key_path'] = $values['alipay_public_key_path'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentOperations(PaymentInterface $payment) {
    $operations = parent::buildPaymentOperations($payment);
    if ($payment->getRemoteState() === 'TRADE_FINISHED') {
      // The signed contract is un-refundable or the refund time is expired
      // (i.e., after 3 months).
      $operations['refund']['access'] = FALSE;
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   *
   * https://docs.open.alipay.com/270/alipay.trade.page.pay/
   */
  protected function doPrepayPayment(PaymentInterface $payment, array $params = NULL) {
    // Alipay accepts 2 decimals.
    $amount = $this->rounder->round($payment->getAmount());
    $out_trade_no = $this->getOutTradeNo($payment);
    $biz_content = [
      'out_trade_no' => $out_trade_no,
      'product_code' => $this->pluginDefinition['product_code'],
      'total_amount' => $amount->getNumber(),
      'subject' => $this->getPaymentSubject($out_trade_no),
      // 'body'
      // 'passback_params'
      // 'qr_pay_mode'
    ];
    if (!empty($params['biz_content'])) {
      $biz_content = array_merge($biz_content, $params['biz_content']);
    }

    $extra_params = [
      'notify_url' => $this->getNotifyUrl()->toString(),
    ];
    if (!empty($params['return_url'])) {
      // The return_url is used by page pay or web pay.
      $extra_params['return_url'] = $params['return_url'];
    }

    return $this->getClient()->buildParams($this->pluginDefinition['api'], $biz_content, $extra_params);
  }

  /**
   * {@inheritdoc}
   *
   * @link https://docs.open.alipay.com/api_1/alipay.trade.query/
   */
  protected function doQueryPayment(PaymentInterface $payment) {
    // Return an array of failure response data (ACQ.TRADE_NOT_EXIST) if the
    // transaction (trade_no) is not created in Alipay.
    $biz_content = [];
    if ($trade_no = $payment->getRemoteId()) {
      $biz_content['trade_no'] = $trade_no;
    }
    else {
      $biz_content['out_trade_no'] = $this->getOutTradeNo($payment);
    }
    return $this->getClient()->query($biz_content);
  }

  /**
   * {@inheritdoc}
   *
   * @link https://docs.open.alipay.com/api_1/alipay.trade.refund/
   */
  protected function doRefundPayment(PaymentInterface $payment, Price $amount) {
    $trade_no = $payment->getRemoteId();
    $out_request_no = $this->getOutRefundNo($payment);
    return $this->getClient()->refund([
      'trade_no' => $trade_no,
      'refund_amount' => $amount->getNumber(),
      // 'refund_reason' => '',
      'out_request_no' => $out_request_no,
    ]);
  }

  /**
   * {@inheritdoc}
   *
   * @link https://docs.open.alipay.com/api_1/alipay.trade.close/
   *
   * @todo: Ignore this operation of closing payment if trade_no is not set,
   * because the transaction is not created in Alipay until the payment is
   * completed? even not after the page is redirected to Alipay?
   */
  protected function doVoidPayment(PaymentInterface $payment) {
    // Return an array of failure response data (ACQ.TRADE_NOT_EXIST or
    // ACQ.TRADE_STATUS_ERROR) if the transaction (trade_no) is not created in
    // Alipay or the status (trade_status) is not 'WAIT_BUYER_PAY'.
    $biz_content = [];
    if ($trade_no = $payment->getRemoteId()) {
      $biz_content['trade_no'] = $trade_no;
    }
    else {
      $biz_content['out_trade_no'] = $this->getOutTradeNo($payment);
    }
    return $this->getClient()->close($biz_content);
  }

  /**
   * {@inheritdoc}
   *
   * @link https://docs.open.alipay.com/270/alipay.trade.page.pay/
   *
   * It's critical to verify the sync IPN almost like onNotify does, because
   * PaymentCheckoutController will redirect the page to 'complete' step and
   * the order will be placed, or the order may be placed with a uncompleted
   * payment.
   *
   * @see \Drupal\commerce_payment\Controller\PaymentCheckoutController::returnPage()
   */
  public function onReturn(OrderInterface $order, Request $request) {
    $ipn_data = $request->query->all();
    $this->logResponse('IPN (onReturn)', $ipn_data, $ipn_data['out_trade_no']);
    $verify_success =
      ($payment = $this->loadPaymentByOutTradeNo($ipn_data['out_trade_no'])) &&
      // Verify IPN.
      $this->verifyIpn($ipn_data, $this->getOutTradeNo($payment), $order->getTotalPrice());

    if (!$verify_success) {
      throw new PaymentGatewayException('Invalid IPN data received.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {
    $ipn_data = $request->request->all();
    if ($this->getClient()->verify($ipn_data, $ipn_data['sign'])) {
      $this->processIpn($ipn_data);
      return new Response('success');
    }
    else {
      return new Response('fail');
    }
  }

  /**
   * {@inheritdoc}
   *
   * The IPN may be received after onReturn() which has already placed the
   * order.
   */
  public function processIpn(array $ipn_data) {
    $this->logResponse('IPN', $ipn_data, $ipn_data['out_trade_no']);

    // Ensure we can load the existing corresponding transaction.
    // We can not invoke loadPaymentByRemoteId() because the 'trade_no' is
    // unknown in 'prepay' phase.
    $payment = $this->loadPaymentByOutTradeNo($ipn_data['out_trade_no']);
    if (!$payment) {
      $this->logIpnIgnored($ipn_data, 'out_trade_no', 'payment object could not be loaded');
      return [
        'processed' => TRUE,
        'message' => 'Ignored: could not load the payment',
      ];
    }

    // Ignore this IPN if it has been already processed, e.g, multiple
    // 'TRADE_SUCCESS' IPNs will be sent on multiple partial refunds or
    // duplicate IPNs on same trade status change.
    if ($payment->getRemoteId() == $ipn_data['trade_no'] && $payment->getRemoteState() == $ipn_data['trade_status']) {
      $this->logIpnIgnored($ipn_data, 'out_trade_no', 'the IPN has been already processed');
      return [
        'processed' => TRUE,
        'message' => 'Ignored: IPN already processed',
      ];
    }

    $payment_success = FALSE;
    if (in_array($ipn_data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
      $payment_success = TRUE;

      if ($payment->getRemoteState() === 'TRADE_SUCCESS' && $ipn_data['trade_status'] === 'TRADE_FINISHED') {
        // The change to trade status happens when the refund date is expired.
        // The order has been already placed, its total price is the payment
        // total amount, the $payment's amount is the user paid amount.
        $total_amount = $payment->getOrder()->getTotalPrice();
      }
      else {
        // The order has not been placed (i.e., is still in draft state), its
        // total price may be refreshed, the $payment's amount is the payment
        // total amount.
        $total_amount = $payment->getAmount();
      }

      // Exit when the IPN verification is failed.
      if (!$this->verifyIpn($ipn_data, $this->getOutTradeNo($payment), $total_amount, FALSE)) {
        $this->logIpnIgnored($ipn_data, 'out_trade_no','payment verification is failed');
        return [
          'processed' => TRUE,
          'message' => 'Ignored: payment verification failed',
        ];
      }

      if (!$payment->getRemoteState() || $payment->getRemoteState() === 'WAIT_BUYER_PAY') {
        // Update the payment amount to an amount the user really paid, see
        // PayPal ExpressCheckout::capturePayment() for an example of setting
        // amount. Setting payment amount to the final amount also ensures the
        // refund amount can not be over this amount.
        if ($buyer_amount = $this->getIpnFinalAmount($payment, $ipn_data)) {
          $payment->setAmount($buyer_amount);
        }
        // Update payment state.
        $payment->setState('completed');
      }
    }
    elseif ($ipn_data['trade_status'] === 'TRADE_CLOSED') {
      // E.g, fully refunded or pending payment expired, handle those
      // cases directly during API response processing.
    }

    // Set remote id.
    $payment->setRemoteId($ipn_data['trade_no']);
    // Update remote state.
    $payment->setRemoteState($ipn_data['trade_status']);
    // Save the transaction information.
    $payment->save();

    if ($payment_success) {
      // Update checkout step of the order and place it after the payment was
      // saved, because the order events may need the payment.
      // Do not invoke $checkout_flow_plugin->redirectToStep() which is for web
      // browser checkout flow, because it will place the order again, save the
      // order and throws a NeedsRedirectException.
      $order = $payment->getOrder();
      if ($order->getState()->value === 'draft') {
        $order->set('checkout_step', 'complete');
        $transition = $order->getState()->getWorkflow()->getTransition('place');
        $order->getState()->applyTransition($transition);
        $order->save();
      }
    }

    // Respond request that we have finished processing this IPN.
    return [
      'processed' => TRUE,
      'message' => 'OK: IPN processed',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @link https://docs.open.alipay.com/204/105301/
   */
  public function verifyIpn(array $ipn_data, $out_trade_no, Price $total_amount, $needs_verify_sign = TRUE) {
    $signature_verified = !$needs_verify_sign || $this->getClient()->verify($ipn_data, $ipn_data['sign']);
    return $signature_verified
      && $ipn_data['out_trade_no'] == $out_trade_no
      && (new Price((string) $ipn_data['total_amount'], $total_amount->getCurrencyCode()))->equals($total_amount)
      && $ipn_data['seller_id'] == $this->configuration['seller_id']
      && $ipn_data['app_id'] == $this->configuration['app_id'];
  }

  /**
   * Gets the final amount of this transaction, it may be the amount a user
   * really paid or a merchant finally received.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment.
   * @param array $ipn_data
   *   The IPN data
   *
   * @return \Drupal\commerce_price\Price|null
   *   An amount or NULL if not resolved.
   */
  protected function getIpnFinalAmount(PaymentInterface $payment, array $ipn_data) {
    if (isset($ipn_data['buyer_pay_amount'])) {
      $final_amount = $ipn_data['buyer_pay_amount'];
    }
    elseif (isset($ipn_data['receipt_amount'])) {
      $final_amount = $ipn_data['receipt_amount'];
    }
    elseif (isset($ipn_data['total_amount'])) {
      $final_amount = $ipn_data['total_amount'];
    }
    if (isset($final_amount)) {
      return new Price((string) $final_amount, $payment->getAmount()->getCurrencyCode());
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   *
   * @todo: verify the conditions: Only ensure the gateway and remote state for Alipay, but not total amount.
   */
  protected function isPaymentReusable(PaymentInterface $payment, PaymentInterface $new_payment) {
    // Payment gateway may change for example a user placed an order on mobile
    // web (Wap) and later pay the order on PC web (Page).
    $same_gateway = $payment->getPaymentGatewayId() == $new_payment->getPaymentGatewayId();
    $trade_closed = $payment->getRemoteState() === 'TRADE_CLOSED';
    return $same_gateway && !$trade_closed;
  }

  /**
   * {@inheritdoc}
   */
  protected function isPrepaySuccessful($prepay_response) {
    return !empty($prepay_response['sign']);
  }

  /**
   * {@inheritdoc}
   *
   * @link https://docs.open.alipay.com/204/105301/
   */
  public function isResponseSuccessful(array $response) {
    if (isset($response['code'])) {
      return $response['code'] === '10000';
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * @link https://docs.open.alipay.com/204/105301/
   */
  public function getResponseError(array $response) {
    if (isset($response['code']) && $response['code'] !== '10000') {
      if (isset($response['sub_code'], $response['sub_msg'])) {
        return sprintf('%s: %s (%s: %s)', $response['sub_code'], $response['sub_msg'], $response['code'], $response['msg']);
      }
      return sprintf('%s: %s', $response['code'], $response['msg']);
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getClient() {
    if (!$this->client) {
      $configuration = $this->configuration;
      $this->client = new AlipayClient(
        $configuration['app_id'],
        $configuration['app_private_key_path'],
        $configuration['alipay_public_key_path'],
        $this->getMode() === 'test'
      );
    }

    return $this->client;
  }

  /**
   * {@inheritdoc}
   */
  public function setClient(AlipayClient $client) {
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public function getRedirectUrl() {
    return $this->getClient()->getUrl();
  }

  /**
   * {@inheritdoc}
   */
  protected function getRemoteState($state_type) {
    switch ($state_type) {
      case self::STATE_NOTPAY:
        return 'WAIT_BUYER_PAY';
      case  self::STATE_CLOSED:
        return 'TRADE_CLOSED';
      case self::STATE_REFUND:
        return 'TRADE_CLOSED';
      case self::STATE_PARTIAL_REFUND:
        return 'TRADE_SUCCESS';
    }

    throw new \InvalidArgumentException('Unrecognized state type: ' . $state_type);
  }

}
