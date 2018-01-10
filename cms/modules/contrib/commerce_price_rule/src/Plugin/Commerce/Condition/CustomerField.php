<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a condition that checks for a user field value.
 *
 * @CommerceCondition(
 *   id = "price_rule_customer_field",
 *   label = @Translation("Field"),
 *   display_label = @Translation("Limit by the value of a user field"),
 *   category = @Translation("Customer"),
 *   entity_type = "user",
 * )
 *
 * @todo Add multiple user fields condition
 * @todo Add user property(ies) condition
 */
class CustomerField extends ConditionBase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new CustomerField object.
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
    $this->entityFieldManager = \Drupal::service('entity_field.manager');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'field_name' => '',
      'field_value' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Get eligible field options.
    $field_name_options = ['' => 'Please select an option'];
    $fields = $this->entityFieldManager->getFieldDefinitions(
      'user',
      'user'
    );
    foreach ($fields as $field) {
      $field_type = $field->getType();
      $field_name = $field->getName();
      // @support other field types than 'string'
      if ($field_type === 'string') {
        if (!isset($field_name_options[$field_name])) {
          $field_name_options[$field_name] = $field->getLabel() . ' (' . $field_name . ')';
        }
      }
    }

    $field_name = '';
    if (!empty($this->configuration['field_name'])) {
      $field_name = $this->configuration['field_name'];
    }

    $form['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Field'),
      '#options' => $field_name_options,
      '#default_value' => $field_name,
      '#required' => TRUE,
      '#description' => $this->t('The field for which to set a condition.'),
    ];

    // @todo Support operators such as equal, not equal, contains

    $field_value = '';
    if (!empty($this->configuration['field_value'])) {
      $field_value = $this->configuration['field_value'];
    }

    $form['field_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#default_value' => $field_value,
      '#description' => $this->t('The value that the field is required to have in order for the condition to pass. Leave empty if you want the condition to pass if the field has no value.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['field_name'] = $values['field_name'];
    $this->configuration['field_value'] = $values['field_value'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $user) {
    $this->assertEntity($user);

    $field_value = $user->get($this->configuration['field_name'])->getValue();

    // If the field has no value and the configuration is set to an empty
    // string, we consider this a match.
    if (!$field_value && !$this->configuration['field_value']) {
      return TRUE;
    }

    if ($field_value[0]['value'] === $this->configuration['field_value']) {
      return TRUE;
    }

    return FALSE;
  }

}
