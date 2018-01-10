<?php

namespace Drupal\commerce_price_rule\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Resolver\PriceResolverInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Returns the price based on the purchasable entity's price field.
 */
class PriceResolver implements PriceResolverInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entity_manager;

  /**
   * Constructs a new PriceResolver object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entity_manager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(
    PurchasableEntityInterface $entity,
    $quantity,
    Context $context
  ) {
    // Load all available price rules for the given store.
    $price_rule_storage = $this->entity_manager->getStorage('commerce_price_rule');
    $price_rules = $price_rule_storage->loadAvailable($context->getStore());

    // Find the first price rule that is available and applicable. Apply it
    // i.e. request a price. If we are given a price, then return it as the
    // result of the price resolution. If we are not given a price, continue to
    // the next price rule.
    foreach ($price_rules as $price_rule) {
      $available = $price_rule->available($entity, $quantity, $context, []);
      if ($available && $price_rule->applies($entity, $quantity, $context, [])) {
        $price = $price_rule->calculate($entity, $quantity, $context);
        if ($price) {
          return $price;
        }
      }
    }
  }

}
