<?php

namespace Drupal\commerce_price_rule;

use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the interface for price rule storage.
 */
interface PriceRuleStorageInterface extends ContentEntityStorageInterface {

  /**
   * Loads the available price rules for the given store.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store.
   *
   * @return \Drupal\commerce_price_rule\Entity\PriceRuleInterface[]
   *   The available price rules.
   */
  public function loadAvailable(StoreInterface $store);

}
