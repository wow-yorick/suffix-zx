<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation;

use Drupal\commerce\Context;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\Core\Database\Connection as DatabaseConnection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides calculation based on a price list.
 *
 * @CommercePriceRuleCalculation(
 *   id = "price_list",
 *   label = @Translation("Get product prices from a list"),
 *   entity_type = "commerce_product_variation",
 * )
 */
class PriceList extends PriceRuleCalculationBase {

  /**
   * The entity manager
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entity_manager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database_connection;

  /**
   * Constructs a new PriceList object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The pluginId for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   The rounder.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Database\Connection $database_connection
   *   The database connection.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RounderInterface $rounder,
    EntityManagerInterface $entity_manager,
    DatabaseConnection $database_connection
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $rounder
    );

    $this->entity_manager = $entity_manager;
    $this->database_connection = $database_connection;
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
      $container->get('commerce_price.rounder'),
      $container->get('entity.manager'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'price_list_id' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);

    $price_list = $this->configuration['price_list_id'];
    if ($price_list) {
      $price_list = $this->entity_manager
        ->getStorage('commerce_price_rule_list')
        ->load($price_list);
    }

    $form['price_list'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Price list'),
      '#description' => $this->t('The list to get the prices from. If no list is provided when creating a new price rule, a list with the same name as the rule will be created and associated with it for convenience.'),
      '#default_value' => $price_list,
      '#target_type' => 'commerce_price_rule_list',
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
    $this->configuration['price_list_id'] = $values['price_list'];
  }

  /**
   * Gets the price list associated with the calculation.
   *
   * @return \Drupal\commerce_price_rule\Entity\PriceListInterface
   *   The calculation's price list.
   */
  public function getList() {
    if (!$this->configuration['price_list_id']) {
      return;
    }

    return $this->entity_manager
      ->getStorage('commerce_price_rule_list')
      ->load($this->configuration['price_list_id']);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t(
      '"@price_list_label" price list',
      ['@price_list_label' => $this->getList()->getName()]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculate(
    EntityInterface $entity,
    PriceRuleInterface $price_rule,
    $quantity,
    Context $context
  ) {
    $this->assertEntity($entity);

    // We should always have a price list associated with the calculation, but
    // let's not break every page on the website where a product price is
    // calculated if there is an error like a form validation error.
    if ((empty($this->configuration['price_list_id']))) {
      return;
    }

    // Performance is important here, load only the required fields directly
    // from the database.
    $query = $this->database_connection
      ->select('commerce_price_rule_list_item', 'li')
      ->fields('li', ['price__number', 'price__currency_code'])
      ->condition('li.price_list_id', $this->configuration['price_list_id'])
      ->condition('li.product_variation_id', $entity->id())
      ->condition('li.status', 1)
      ->range(0, 1);

    // The quantity must be matching the minimum and maximum quantites of the
    // list item i.e. it should either be both higher or equal than the minimum
    // and lower or equal than the maximum. When no maximum is defied, which
    // should happen at the last list item for a product varation (e.g. price X
    // for more than Y number of items), the quantity only needs to be higher
    // than the minimum.
    $quantity_max_defined = $query->andConditionGroup()
      ->condition('min_quantity', $quantity, '<=')
      ->condition('max_quantity', $quantity, '>=');
    $quantity_max_undefined = $query->andConditionGroup()
      ->condition('min_quantity', $quantity, '<=')
      ->condition('max_quantity', NULL, 'IS NULL');
    $quantity_query = $query->orConditionGroup()
      ->condition($quantity_max_defined)
      ->condition($quantity_max_undefined);
    $query->condition($quantity_query);

    $result = $query
      ->execute()
      ->fetchAssoc();

    if ($result) {
      return new Price(
        $result['price__number'],
        $result['price__currency_code']
      );
    }
  }

}
