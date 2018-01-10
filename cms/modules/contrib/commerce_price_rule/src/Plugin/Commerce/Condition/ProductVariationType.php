<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\Condition;

use Drupal\commerce\EntityHelper;
use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the product variation type condition.
 *
 * @CommerceCondition(
 *   id = "price_rule_product_variation_type",
 *   label = @Translation("Product Variation Type"),
 *   display_label = @Translation("Limit by product variation type"),
 *   category = @Translation("Product"),
 *   entity_type = "commerce_product_variation",
 * )
 */
class ProductVariationType extends ConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The product variation type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $product_variation_type_storage;

  /**
   * Constructs a new ProductVariationType object.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->product_variation_type_storage = $entity_type_manager->getStorage('commerce_product_variation_type');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'product_variation_types' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $product_variation_types = EntityHelper::extractLabels(
      $this->product_variation_type_storage->loadMultiple()
    );
    $form['product_variation_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Product variation types'),
      '#options' => $product_variation_types,
      '#default_value' => $this->configuration['product_variation_types'],
      '#tags' => TRUE,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $product_variation_types = array_filter($values['product_variation_types']);
    if ($product_variation_types) {
      $this->configuration['product_variation_types'] = $product_variation_types;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    return in_array(
      $entity->bundle(),
      $this->configuration['product_variation_types']
    );
  }

}
