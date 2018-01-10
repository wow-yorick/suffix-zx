<?php

namespace Drupal\commerce_price_rule\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the price rule calculation plugin annotation object.
 *
 * Plugin namespace: Plugin\Commerce\PriceRuleCalculation.
 *
 * @Annotation
 */
class CommercePriceRuleCalculation extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The calculation entity type ID.
   *
   * This is the entity type ID of the entity passed to the plugin during
   * execution. For example: 'commerce_product_variation'.
   *
   * @var string
   */
  public $entity_type;

}
