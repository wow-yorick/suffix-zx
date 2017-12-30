<?php

namespace Drupal\commerce_cnpay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\Exception as WeChatException;
use EasyWeChat\Kernel\Support;
use EasyWeChat\Payment\Application;
use EasyWeChat\Payment\Kernel\Exceptions\InvalidSignException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for WeChat payments.
 *
 * Do not connect the user identified by openid when processing IPN, because a
 * user's order can be paid by an another user.
 */
class WeChatPaymentGatewayBase extends CNPaymentGatewayBase implements WeChatPaymentGatewayInterface {

  /**
   * The WeChat Pay client.
   *
   * @var \EasyWeChat\Payment\Application
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
    LoggerChannelFactoryInterface $logger_factory
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

    if (empty($plugin_definition['trade_type'])) {
      throw new InvalidPluginDefinitionException($plugin_id, 'Plugin trade_type is not defined.');
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
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'dev_notify_domain' => '',
      'app_id' => '',
      'mch_id' => '',
      'key' => '',
      'cert_path' => '',
      'key_path' => '',
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
    $form['mch_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant ID'),
      '#default_value' => $this->configuration['mch_id'],
      '#required' => TRUE,
    ];
    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#default_value' => $this->configuration['key'],
      '#required' => TRUE,
    ];
    $form['cert_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cert path'),
      '#default_value' => $this->configuration['cert_path'],
      '#required' => TRUE,
    ];
    $form['key_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key path'),
      '#default_value' => $this->configuration['key_path'],
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
    if (!file_exists($values['cert_path'])) {
      $form_state->setError($form['cert_path'], 'The cert at given path does not exist');
    }
    if (!file_exists($values['key_path'])) {
      $form_state->setError($form['key_path'], 'The key at given path does not exist');
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
      $this->configuration['mch_id'] = $values['mch_id'];
      $this->configuration['key'] = $values['key'];
      $this->configuration['cert_path'] = $values['cert_path'];
      $this->configuration['key_path'] = $values['key_path'];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doPrepayPayment(PaymentInterface $payment, array $params = NULL) {
    // WeChat only accepts integer values in cents.
    $amount = $payment->getAmount()->multiply('100');
    $out_trade_no = $this->getOutTradeNo($payment);
    $data = [
      'trade_type' => $this->pluginDefinition['trade_type'],
      'body' => $this->getPaymentSubject($out_trade_no),
      'out_trade_no' => $out_trade_no,
      'fee_type' => $amount->getCurrencyCode(),
      'total_fee' => (int) $amount->getNumber(),
      // 'notify_url' => 'http://xxx.com/order-notify', // override global settings
    ];
    if ($params) {
      $data = array_merge($data, $params);
    }

    return $this->getClient()->order->unify($data);
  }

  /**
   * {@inheritdoc}
   */
  protected function doQueryPayment(PaymentInterface $payment) {
    // Try the transaction_id recommended by WeChat first.
    if ($transaction_id = $payment->getRemoteId()) {
      return $this->getClient()->order->queryByTransactionId($transaction_id);
    }
    return $this->getClient()->order->queryByOutTradeNumber($this->getOutTradeNo($payment));
  }

  /**
   * {@inheritdoc}
   */
  protected function doRefundPayment(PaymentInterface $payment, Price $amount) {
    $transaction_id = $payment->getRemoteId();
    $out_refund_no = $this->getOutRefundNo($payment);
    $total_fee = (int) $payment->getOrder()->getTotalPrice()->multiply('100')->getNumber();
    $refund_fee = (int) $amount->multiply('100')->getNumber();
    return $this->getClient()->refund->byTransactionId($transaction_id, $out_refund_no, $total_fee, $refund_fee, [
      'refund_fee_type' => $amount->getCurrencyCode(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function doVoidPayment(PaymentInterface $payment) {
    // Void an existing payment to avoid paying the order with stale
    // params, and we can not get a new QR code for the order with stale
    // params, an error will arise:
    // [
    //   'return_code'  => 'SUCCESS',
    //   'result_code'  => 'FAIL',
    //   'err_code'     => 'INVALID_REQUEST',
    //   'err_code_des' => '201 Duplicate out trade no',
    // ]
    return $this->getClient()->order->close($this->getOutTradeNo($payment));
  }

  /**
   * {@inheritdoc}
   *
   * The WeChat payment flow:
   *
   * states:
   *   NOTPAY:
   *   CLOSED:
   *   PAYERROR:
   *   SUCCESS:
   *   REFUND:
   * transitions:
   *   close:
   *     from: [NOTPAY]
   *     to: CLOSED
   *   pay_error:
   *     from: [NOTPAY] # can be from 'PAYERROR'?
   *     to: PAYERROR
   *   pay_success:
   *     from: [NOTPAY]
   *     to: SUCCESS
   *   refund:
   *     from: [SUCCESS]
   *     to: REFUND
   *
   * @see https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_1
   *
   * The payment flow documented on above link may be not accurate, see ERROR
   * CODEs at the end of 'close order' document:
   *
   * @see https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_3
   */
  public function onNotify(Request $request) {
    // Sandbox mode.
    if ($this->getMode() === 'test') {
      return NULL;
    }

    try {
      $that = $this;
      return $this->getClient($request)->handlePaidNotify(function (array $ipn) use ($that) {
        $result = $that->processIpn($ipn);
        return $result['processed'] ?: $result['message'];
      });
    }
    catch (WeChatException $e) {
      // Possible errors:
      // 1. Parsing IPN xml data failed:   'Invalid request XML: ...'
      // 2. Signature verification failed: InvalidSignException
      $this->logger->error($e->getMessage());
      $data = [
        'return_code' => 'FAIL',
        'return_msg' => $e instanceof InvalidSignException ? 'Invalid signature' : $e->getMessage(),
      ];
      return new Response(Support\XML::build($data));
    }
  }

  public function onRefundNotify(Request $request) {
    try {
      $that = $this;
      return $this->getClient($request)->handleRefundedNotify(function (array $ipn) use ($that) {
        // @todo: instead check $ipn_data['refund_status'] === 'SUCCESS'
        $result = $that->processIpn($this->reqInfo);
        return $result['processed'] ?: $result['message'];
      });
    }
    catch (WeChatException $e) {
      $this->logger->error($e->getMessage());
      $data = [
        'return_code' => 'FAIL',
        'return_msg' => $e instanceof InvalidSignException ? 'Invalid signature' : $e->getMessage(),
      ];
      return new Response(Support\XML::build($data));
    }
  }

  /**
   * {@inheritdoc}
   *
   * The IPN is only received on Payment success or failure.
   */
  public function processIpn(array $ipn_data) {
    if (empty($ipn_data['out_trade_no'])) {
      $this->logResponse('IPN', $ipn_data, 'missing out trade no');
      // We cannot do anything if the out trade no is unknown, this might or not
      // happen. WeChat says this happens when WeChat sends a failure IPN:
      // <xml><return_code>FAIL</return_code></xml>.
      // But in practise that does not make sense and we haven't encountered it.
      return [
        'processed' => TRUE,
        'message' => 'Ignored: failure IPN received',
      ];
    }

    $this->logResponse('IPN', $ipn_data, $ipn_data['out_trade_no']);

    // Ensure we can load the existing corresponding transaction.
    // We can not invoke loadPaymentByRemoteId() because the 'transaction_id' is
    // unknown in 'prepay' phase.
    $payment = $this->loadPaymentByOutTradeNo($ipn_data['out_trade_no']);
    if (!$payment) {
      $this->logIpnIgnored($ipn_data, 'out_trade_no', 'payment object could not be loaded');
      return [
        'processed' => TRUE,
        'message' => 'Ignored: could not load the payment',
      ];
    }

    // Ignore this IPN if it has been already processed, e.g., duplicate
    // IPNs may be sent on a same 'SUCCESS' payment.
    if ($payment->getRemoteId() == $ipn_data['transaction_id'] && $payment->getRemoteState() === ($ipn_data['result_code'] === 'SUCCESS' ? 'SUCCESS' : 'PAYERROR')) {
      $this->logIpnIgnored($ipn_data, 'out_trade_no', 'the IPN has been already processed');
      return [
        'processed' => TRUE,
        'message' => 'Ignored: IPN already processed',
      ];
    }

    $payment_success = FALSE;
    if ($ipn_data['result_code'] === 'SUCCESS') {
      $payment_success = TRUE;

      // The payment is not completed, hence it's amount is equivalent of the
      // order's total amount.
      $total_amount = $payment->getAmount();
      // Exit when the IPN verification is failed.
      if (!$this->verifyIpn($ipn_data, $this->getOutTradeNo($payment), $total_amount, FALSE)) {
        $this->logIpnIgnored($ipn_data, 'out_trade_no', 'payment verification is failed');
        return [
          'processed' => TRUE,
          'message' => 'Ignored: payment verification failed',
        ];
      }

      if (!$payment->getRemoteState() || $payment->getRemoteState() === 'NOTPAY' || $payment->getRemoteState() === 'PAYERROR') {
        // Update the payment amount to an amount the user really paid, see
        // PayPal ExpressCheckout::capturePayment() for an example of setting
        // amount. Setting payment amount to the final amount also ensures the
        // refund amount can not be over this amount.
        if ($buyer_amount = $this->getIpnFinalAmount($payment, $ipn_data)) {
          $payment->setAmount($buyer_amount);
        }
        // Update payment state.
        $payment->setState('completed');
        // Update remote state.
        $payment->setRemoteState('SUCCESS');
      }
    }
    else {
      // Update remote state .
      $payment->setRemoteState('PAYERROR');
      // Do not update order checkout step (e.g., to previous step of 'review').
    }

    // Set remote id.
    $payment->setRemoteId($ipn_data['transaction_id']);
    // Save the transaction information.
    $payment->save();

    if ($payment_success) {
      // Update checkout step of the order and place it after the payment was
      // saved, because the order events may need the payment.
      // Do not invoke $checkout_flow_plugin->redirectToStep() which is for web
      // browser checkout flow, because it will place the order again, save the
      // order and throws a NeedsRedirectException.
      $order = $payment->getOrder();
      // Only update the order in draft state, because the order might have
      // been placed after onReturn() is called.
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
   */
  public function verifyIpn(array $ipn_data, $out_trade_no, Price $total_amount, $needs_verify_sign = TRUE) {
    $sign = $ipn_data['sign'];
    unset($ipn_data['sign']);
    $sign_verified = !$needs_verify_sign || $sign === Support\generate_sign($ipn_data, $this->configuration['key']);

    if ($sign_verified) {
      // Verify IPN details.
      $total_amount = $total_amount->multiply('100');
      $fee_type = isset($ipn_data['fee_type']) ? $ipn_data['fee_type'] : $total_amount->getCurrencyCode();
      return $ipn_data['out_trade_no'] == $out_trade_no
        && (new Price($ipn_data['total_fee'], $fee_type))->equals($total_amount)
        && $ipn_data['mch_id'] == $this->configuration['mch_id']
        && $ipn_data['appid'] == $this->configuration['app_id'];
    }
    return FALSE;
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
    if (isset($ipn_data['cash_fee'])) {
      $final_amount = $ipn_data['cash_fee'];
    }
    elseif (isset($ipn_data['total_fee'])) {
      $final_amount = $ipn_data['total_fee'];
    }
    if (isset($final_amount)) {
      return (new Price($final_amount, $payment->getAmount()->getCurrencyCode()))->divide('100');
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function isPrepaySuccessful($prepay_response) {
    if (isset($prepay_response['return_code']) && $prepay_response['return_code'] === 'SUCCESS') {
      return $prepay_response['result_code'] === 'SUCCESS';
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isResponseSuccessful(array $response) {
    if (isset($response['return_code']) && $response['return_code'] === 'SUCCESS') {
      return $response['result_code'] === 'SUCCESS';
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseError(array $response) {
    if (isset($response['return_code'])) {
      if ($response['return_code'] !== 'SUCCESS') {
        return $response['return_msg'];
      }
      if ($response['result_code'] !== 'SUCCESS') {
        return sprintf('%s: %s', $response['err_code'], $response['err_code_des']);
      }
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getClient(Request $request = NULL) {
    if (!$this->client) {
      $configuration = $this->getConfiguration();
      $payment = Factory::payment([
        // 'response_type' => 'array',
        'http' => [
          'timeout'  => 10,
        ],
        'log' => [
          'level'    => $this->getMode() === 'live' ? 'warning' : 'debug',
          'file'     => '/tmp/easywechat.log',
        ],
        // Payments.
        'sandbox'    => $configuration['mode'] === 'test',
        'app_id'     => $configuration['app_id'],
        'mch_id'     => $configuration['mch_id'],
        'key'        => $configuration['key'],
        'cert_path'  => $configuration['cert_path'],
        'key_path'   => $configuration['key_path'],
        'notify_url' => $this->getNotifyUrl()->toString(),
      ]);
      if ($request) {
        // Set the given request instead.
        $payment['request'] = function ($app) use ($request) {
          return $request;
        };
      }

      $this->client = $payment;
    }

    return $this->client;
  }

  /**
   * {@inheritdoc}
   */
  public function setClient(Application $client) {
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  protected function getRemoteState($state_type) {
    switch ($state_type) {
      case self::STATE_NOTPAY:
        return 'NOTPAY';
      case  self::STATE_CLOSED:
        return 'CLOSED';
      case self::STATE_REFUND:
      case self::STATE_PARTIAL_REFUND:
        return 'REFUND';
    }

    throw new \InvalidArgumentException('Unrecognized state type: ' . $state_type);
  }

  //  // Used for $payment->setExpiresTime(), but currently it does not make sense,
  //  // because the prepay_id or code_url can be refreshed.
  //  private function getPaymentExpiresTime(array $params) {
  //    if (!empty($params['time_expire'])) {
  //      // WeChat always makes use of Beijing time.
  //      $zone = new \DateTimeZone('Asia/Shanghai');
  //      $date_format = \DateTime::createFromFormat('YmdHis', $params['time_expire'], $zone);
  //      return $date_format->getTimestamp();
  //    }
  //    // TTL of both prepay_id and code_url are 2h.
  //    return $this->time->getRequestTime() + 7200;
  //  }

}
