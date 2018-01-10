<?php

namespace Drupal\commerce_price_rule\Entity;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_store\Entity\EntityStoresInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the interface for price rules.
 */
interface PriceRuleInterface extends ContentEntityInterface, EntityStoresInterface {

  /**
   * Gets the price rule name.
   *
   * @return string
   *   The price rule name.
   */
  public function getName();

  /**
   * Sets the price rule name.
   *
   * @param string $name
   *   The price rule name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the price rule description.
   *
   * @return string
   *   The price rule description.
   */
  public function getDescription();

  /**
   * Sets the price rule description.
   *
   * @param string $description
   *   The price rule description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Gets the calculation.
   *
   * @return \Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\PriceRuleCalculationInterface|null
   *   The calculation, or NULL if not yet available.
   */
  public function getCalculation();

  /**
   * Sets the calculation plugin.
   *
   * @param \Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\PriceRuleCalculationInterface $calculation
   *   The calculation plugin.
   *
   * @return $this
   */
  public function setCalculation($calculation);

  /**
   * Gets the conditions.
   *
   * @return \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface[]
   *   The conditions.
   */
  public function getConditions();

  /**
   * Sets the conditions.
   *
   * @param \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface[]
   *   The conditions.
   *
   * @return $this
   */
  public function setConditions(array $conditions);

  /**
   * Gets the price rule start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The price rule start date.
   */
  public function getStartDate();

  /**
   * Sets the price rule start date.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $start_date
   *   The price rule start date.
   *
   * @return $this
   */
  public function setStartDate(DrupalDateTime $start_date);

  /**
   * Gets the price rule end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The price rule end date.
   */
  public function getEndDate();

  /**
   * Sets the price rule end date.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $end_date
   *   The price rule end date.
   *
   * @return $this
   */
  public function setEndDate(DrupalDateTime $end_date = NULL);

  /**
   * Get whether the price rule is enabled.
   *
   * @return bool
   *   TRUE if the price rule is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets whether the price rule is enabled.
   *
   * @param bool $enabled
   *   Whether the price rule is enabled.
   *
   * @return $this
   */
  public function setEnabled($enabled);

  /**
   * Gets the weight.
   *
   * @return int
   *   The weight.
   */
  public function getWeight();

  /**
   * Sets the weight.
   *
   * @param int $weight
   *   The weight.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Checks whether the price rule is available for the product and its context.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The product.
   *
   * @param string $quantity
   *   The quantity of products.
   *
   * @param \Drupal\commerce\Context;
   *   The context, which includes the customer, the store and the time.
   *
   * @return bool
   *   TRUE if the price rule is available, FALSE otherwise.
   */
  public function available(
    PurchasableEntityInterface $entity,
    $quantity,
    Context $context
  );

  /**
   * Checks whether the price rule can be applied to the given product.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The product.
   *
   * @param string $quantity
   *   The quantity of products.
   *
   * @param \Drupal\commerce\Context;
   *   The context, which includes the customer, the store and the time.
   *
   * @return bool
   *   TRUE if the price rule can be applied, FALSE otherwise.
   */
  public function applies(
    PurchasableEntityInterface $entity,
    $quantity,
    Context $context
  );

  /**
   * Calculate the price rule for the given product.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The product.
   *
   * @param string $quantity
   *   The quantity of products.
   *
   * @param \Drupal\commerce\Context;
   *   The context, which includes the customer, the store and the time.
   *
   * @return \Drupal\commerce\Price|NULL
   *   The calculated price if it can be calculated, NULL otherwise.
   */
  public function calculate(
    PurchasableEntityInterface $entity,
    $quantity,
    Context $context
  );
}
