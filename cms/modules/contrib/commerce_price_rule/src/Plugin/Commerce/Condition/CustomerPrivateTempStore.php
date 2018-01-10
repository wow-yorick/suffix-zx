<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a condition that checks for a value in the user's PrivateTempStore.
 *
 * @CommerceCondition(
 *   id = "price_rule_customer_tempstore",
 *   label = @Translation("Private Temporary Store Value"),
 *   display_label = @Translation("Limit by a value in the user's private temporary store (for developer use only)"),
 *   category = @Translation("Customer"),
 *   entity_type = "user",
 * )
 */
class CustomerPrivateTempStore extends ConditionBase {

  /**
   * The user's private temporary store.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $privateTempStore;

  /**
   * Constructs a new CustomerPrivateTempStore object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // @todo Figure out why proper dependency injection does not work
    $this->privateTempStore = \Drupal::service('user.private_tempstore');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'private_tempstore' => '',
      'key' => '',
      'value' => '',
      'in_array' => 0,
      'match_null' => 0,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['private_tempstore'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Store name'),
      '#default_value' => $this->configuration['private_tempstore'],
      '#required' => TRUE,
      '#description' => $this->t('The name of the private temporary store that holds the entry'),
    ];

    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#default_value' => $this->configuration['key'],
      '#required' => TRUE,
      '#description' => $this->t('The key of the entry in the user\'s private temporary store that should be matching the value.'),
    ];

    $form['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#default_value' => $this->configuration['value'],
      '#description' => $this->t('The value that the entry is required to have in order for the condition to pass. Leave empty if you want the condition to pass if the entry is not set or it has an empty string as its value.'),
    ];

    // @todo Support operators such as equal, not equal, contains

    $form['in_array'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('The value is an array element'),
      '#default_value' => $this->configuration['in_array'],
      '#description' => $this->t('When checked, the entry\'s value will need to be an array with one of its elements matching the expected value.'),
    ];

    $form['match_null'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Pass if the entry does not exist'),
      '#default_value' => $this->configuration['match_null'],
      '#description' => $this->t('When checked, the condition will pass if the entry does not exist. If the entry exist, its value will still be compared to the defined value.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['private_tempstore'] = $values['private_tempstore'];
    $this->configuration['key'] = $values['key'];
    $this->configuration['value'] = $values['value'];
    $this->configuration['in_array'] = $values['in_array'];
    $this->configuration['match_null'] = $values['match_null'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $user) {
    $this->assertEntity($user);

    $value = $this->privateTempStore
      ->get($this->configuration['private_tempstore'])
      ->get($this->configuration['key']);

    // If the value is not set, respond according to configuration.
    if ($value === NULL) {
      if ($this->configuration['match_null']) {
        return TRUE;
      }

      return FALSE;
    }

    // If we are expecting an array, look for an element in the value. Do not
    // pass if the value is not an array.
    if ($this->configuration['in_array']) {
      if (is_array($value) && in_array($this->configuration['value'], $value)) {
        return TRUE;
      }

      return FALSE;
    }

    // Otherwise, we only support equals comparison for now.
    if ($value == $this->configuration['value']) {
      return TRUE;
    }

    return FALSE;
  }

}
