<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\EntityTrait;

use Drupal\commerce\BundleFieldDefinition;
use Drupal\commerce\Plugin\Commerce\EntityTrait\EntityTraitBase;

/**
 * Provides the "purchasable_entity_dimensions" trait.
 *
 * @CommerceEntityTrait(
 *   id = "purchasable_entity_dimensions",
 *   label = @Translation("Has dimensions"),
 *   entity_types = {"commerce_product_variation"}
 * )
 */
class PurchasableEntityDimensions extends EntityTraitBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];
    $fields['dimensions'] = BundleFieldDefinition::create('physical_dimensions')
      ->setLabel('Dimensions')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'physical_dimensions_default',
        'weight' => 90,
      ]);

    return $fields;
  }

}
