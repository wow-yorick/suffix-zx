<?php

namespace Drupal\commerce_price_rule\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Price list item entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_price_rule_list_item",
 *   label = @Translation("Price List Item"),
 *   label_collection = @Translation("Price List Items"),
 *   label_singular = @Translation("price list item"),
 *   label_plural = @Translation("price list items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count price list item",
 *     plural = "@count price list items",
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_price_rule\Form\PriceListItemForm",
 *       "add" = "Drupal\commerce_price_rule\Form\PriceListItemForm",
 *       "edit" = "Drupal\commerce_price_rule\Form\PriceListItemForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_price_rule_list_item",
 *   admin_permission = "administer commerce_price_rule",
 *   entity_keys = {
 *     "id" = "price_list_item_id",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/price-rules/price-lists/{commerce_price_rule_list}/items/add",
 *     "edit-form" = "/admin/commerce/price-rules/price-lists/items/{commerce_price_rule_list_item}/edit",
 *     "delete-form" = "/admin/commerce/price-rules/price-lists/items/{commerce_price_rule_list_item}/delete",
 *   },
 * )
 */
class PriceListItem extends ContentEntityBase implements PriceListItemInterface {

  /**
   * {@inheritdoc}
   */
  public function getPriceList() {
    return $this->get('price_list_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriceListId() {
    return $this->get('price_list_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getProductVariation() {
    return $this->get('product_variation_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getProductVariationId() {
    return $this->get('product_variation_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrice() {
    return $this->get('price')->first()->toPrice();
  }

  /**
   * {@inheritdoc}
   */
  public function setPrice(Price $price) {
    $this->set('price', $price);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMinimumQuantity() {
    return (string) $this->get('min_quantity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMinimumQuantity($quantity) {
    $this->set('min_quantity', (string) $quantity);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaximumQuantity() {
    return (string) $this->get('max_quantity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMaximumQuantity($quantity) {
    $this->set('max_quantity', (string) $quantity);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->set('status', (bool) $enabled);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['price_list_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Price list'))
      ->setDescription(t('The parent price list.'))
      ->setSetting('target_type', 'commerce_price_rule_list')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['product_variation_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Product variation'))
      ->setDescription(t('The product variation that the price should be applied to.'))
      ->setSetting('target_type', 'commerce_product_variation')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Price'))
      ->setDescription(t('The price list item price'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['min_quantity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Minimum Quantity'))
      ->setDescription(t('The minimum number of purchased units that the price will apply to.'))
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 1)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'commerce_quantity',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['max_quantity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Maximum Quantity'))
      ->setDescription(t('The maximum number of purchased units that the price will apply to.'))
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 1)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'commerce_quantity',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Enabled'))
      ->setDescription(t('Whether the price list item is enabled.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 99,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
