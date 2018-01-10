<?php

namespace Drupal\commerce_price_rule\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the interface for price lists.
 */
interface PriceListInterface extends ContentEntityInterface {

  /**
   * Gets the price list name.
   *
   * @return string
   *   The price list name.
   */
  public function getName();

  /**
   * Sets the price list name.
   *
   * @param string $name
   *   The price list name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the price list description.
   *
   * @return string
   *   The price list description.
   */
  public function getDescription();

  /**
   * Sets the price list description.
   *
   * @param string $description
   *   The price list description.
   *
   * @return $this
   */
  public function setDescription($description);

}
