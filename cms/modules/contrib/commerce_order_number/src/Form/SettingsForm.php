<?php

namespace Drupal\commerce_order_number\Form;

use Drupal\commerce_order_number\OrderNumber;
use Drupal\commerce_order_number\OrderNumberFormatterInterface;
use Drupal\commerce_order_number\OrderNumberGeneratorManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure commerce_order_number settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The order number generator manager.
   *
   * @var \Drupal\commerce_order_number\OrderNumberGeneratorManager
   */
  protected $orderNumberGeneratorManager;

  /**
   * The order number formatter.
   *
   * @var \Drupal\commerce_order_number\OrderNumberFormatterInterface
   */
  protected $orderNumberFormatter;

  /**
   * Constructs a new SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\commerce_order_number\OrderNumberGeneratorManager $order_number_generator_manager
   *   The order number generator manager.
   * @param \Drupal\commerce_order_number\OrderNumberFormatterInterface $order_number_formatter
   *   The order number formatter.
   */
  public function __construct(ConfigFactoryInterface $config_factory, OrderNumberGeneratorManager $order_number_generator_manager, OrderNumberFormatterInterface $order_number_formatter) {
    parent::__construct($config_factory);

    $this->orderNumberGeneratorManager = $order_number_generator_manager;
    $this->orderNumberFormatter = $order_number_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.commerce_order_number_generator'),
      $container->get('commerce_order_number.order_number_formatter')
    );
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['commerce_order_number.settings'];
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'commerce_order_number_settings';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('commerce_order_number.settings');

    $form['generator'] = [
      '#type' => 'details',
      '#title' => $this->t('Order number generation'),
      '#open' => TRUE,
    ];

    $generator_plugins = array_map(function ($definition) {
      return sprintf('%s (%s)', $definition['label'], $definition['description']);
    }, $this->orderNumberGeneratorManager->getDefinitions());

    $form['generator']['generator'] = [
      '#type' => 'select',
      '#options' => $generator_plugins,
      '#required' => TRUE,
      '#default_value' => $config->get('generator'),
      '#title' => $this->t('Generator plugin'),
      '#description' => $this->t('Choose the plugin to be used for order number generation.'),
    ];

    $form['formatting'] = [
      '#type' => 'details',
      '#title' => $this->t('Order number formatting'),
      '#open' => TRUE,
    ];

    $form['formatting']['padding'] = [
      '#type' => 'number',
      '#default_value' => $config->get('padding'),
      '#min' => 0,
      '#step' => 1,
      '#title' => $this->t('Order number padding'),
      '#description' => $this->t('Pad the order number with leading zeroes. Example: a value of 6 will output order number 52 as 000052.'),
    ];

    $pattern_help_text = $this->t('In addition to the generation method, a
    pattern for the order number can be set, e.g. to pre- or suffix the
    calculated number. The placeholder %order_number is replaced with the
    generated number and *must* be included in the pattern. If you are using the
    yearly pattern, the placeholder %year_placeholder must be included as well.
    For the montly pattern, additionally the placeholder %month_placeholder is
    mandatory.', [
      '%order_number' => OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_ORDER_NUMBER,
      '%year_placeholder' => OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_YEAR,
      '%month_placeholder' => OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_MONTH,
    ]);
    $form['formatting']['pattern'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $config->get('pattern'),
      '#title' => $this->t('Order number pattern'),
      '#description' => $pattern_help_text,
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced settings'),
      '#open' => FALSE,
    ];

    $force_help_text = $this->t('If checked, the order number generation will
    force to set its generated order number on the given order entity, even if
    there is already an order number set. This covers a very hypothetical
    situation, as this cannot happen in a normal setup, as the order number
    generation is only called during order placement, and Commerce itself does
    not set any order number before order placement. So this situation may never
    happen without having a custom module running that does set an order number
    in an earlier state.');
    $form['advanced']['force'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('force'),
      '#title' => $this->t('Force override'),
      '#description' => $force_help_text,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('commerce_order_number.settings')
      ->set('generator', $form_state->getValue('generator'))
      ->set('padding', $form_state->getValue('padding'))
      ->set('pattern', $form_state->getValue('pattern'))
      ->set('force', $form_state->getValue('force'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $generator = $form_state->getValue('generator');
    $padding = $form_state->getValue('padding');
    $pattern = $form_state->getValue('pattern');

    // Trim pattern value, then set the trimmed value.
    $pattern = trim($pattern);
    $form_state->setValue('pattern', $pattern);

    // Do not allow whitespace characters.
    if (preg_match('/\s/', $pattern)) {
      $form_state->setErrorByName('pattern', $this->t('The pattern field must not contain whitespace characters!'));
    }

    // We must ensure, that all necessary placeholders are present.
    $required_placeholders = [OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_ORDER_NUMBER];
    switch ($generator) {
      case 'yearly':
        $required_placeholders[] = OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_YEAR;
        break;

      case 'monthly':
        $required_placeholders[] = OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_MONTH;
        $required_placeholders[] = OrderNumberFormatterInterface::PATTERN_PLACEHOLDER_YEAR;
        break;
    }

    foreach ($required_placeholders as $required_placeholder) {
      if (strpos($pattern, $required_placeholder) === FALSE) {
        $form_state->setErrorByName('pattern', $this->t('Invalid pattern. Required %placeholder placeholder is missing.', ['%placeholder' => $required_placeholder]));
      }
    }

    // Now, check the resulting length. Not the pattern's length is crucial, but
    // the resulting order number's length.
    $max_length = 255;
    $example_order_number = new OrderNumber(1, date('Y'), date('m'));
    $example_order_number_formatted = $this->orderNumberFormatter->format($example_order_number, $padding, $pattern);

    if (strlen($example_order_number_formatted) > $max_length) {
      $form_state->setErrorByName('pattern', $this->t('The generated order number must not exceed %max_length characters. Current settings leads to order numbers having %actual_length characters, e.g. %example_number.', [
        '%max_length' => $max_length,
        '%actual_length' => strlen($example_order_number_formatted),
        '%example_number' => $example_order_number_formatted,
      ]));
    }
  }

}
