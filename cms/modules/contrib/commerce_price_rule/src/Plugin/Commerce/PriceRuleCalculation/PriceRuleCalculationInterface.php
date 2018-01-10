<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation;

use Drupal\commerce\Context;
use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for price rule calculations.
 */
interface PriceRuleCalculationInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Gets the calculation entity type ID.
   *
   * This is the entity type ID of the entity passed to apply().
   *
   * @return string
   *   The calculation's entity type ID.
   */
  public function getEntityTypeId();

  /**
   * Gets an explanatory label for the calculation.
   *
   * For example, a calculation that calculates the price as a percentage off
   * the base product price could return a label "20% off the product price".
   *
   * @return string
   *   The label.
   */
  public function getLabel();

  /**
   * Applies the calculation to the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\commerce_price_rule\Entity\PriceRuleInterface $price_rule
   *   THe parent price rule.
   * @param int $quantity
   *   The quantity.
   * @param \Drupal\commerce\Context $context
   *   The commerce context.
   */
  public function calculate(
    EntityInterface $entity,
    PriceRuleInterface $price_rule,
    $quantity,
    Context $context
  );

}
