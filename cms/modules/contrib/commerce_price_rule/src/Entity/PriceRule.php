<?php

namespace Drupal\commerce_price_rule\Entity;

use Drupal\commerce\ConditionGroup;
use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the price rule entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_price_rule",
 *   label = @Translation("Price Rule"),
 *   label_collection = @Translation("Price Rules"),
 *   label_singular = @Translation("price rule"),
 *   label_plural = @Translation("price rules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count price rule",
 *     plural = "@count price rules",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\commerce_price_rule\PriceRuleStorage",
 *     "access" = "Drupal\commerce\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\commerce\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_price_rule\PriceRuleListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_price_rule\Form\PriceRuleForm",
 *       "add" = "Drupal\commerce_price_rule\Form\PriceRuleForm",
 *       "edit" = "Drupal\commerce_price_rule\Form\PriceRuleForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "commerce_price_rule",
 *   data_table = "commerce_price_rule_field_data",
 *   admin_permission = "administer commerce_price_rule",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "price_rule_id",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "add-form" = "/price-rule/add",
 *     "edit-form" = "/price-rule/{commerce_price_rule}/edit",
 *     "delete-form" = "/price-rule/{commerce_price_rule}/delete",
 *     "collection" = "/admin/commerce/price-rules",
 *   },
 * )
 */
class PriceRule extends ContentEntityBase implements PriceRuleInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStores() {
    return $this->get('stores')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function setStores(array $stores) {
    $this->set('stores', $stores);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStoreIds() {
    $store_ids = [];
    foreach ($this->get('stores') as $field_item) {
      $store_ids[] = $field_item->target_id;
    }
    return $store_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function setStoreIds(array $store_ids) {
    $this->set('stores', $store_ids);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCalculation() {
    if (!$this->get('calculation')->isEmpty()) {
      return $this->get('calculation')->first()->getTargetInstance();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setCalculation($calculation) {
    $this->calculation->target_plugin_id = $calculation->getPluginId();
    $this->calculation->target_plugin_configuration = $calculation->getConfiguration();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    $conditions = [];
    foreach ($this->get('conditions') as $field_item) {
      /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItemInterface $field_item */
      $conditions[] = $field_item->getTargetInstance();
    }
    return $conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditions(array $conditions) {
    $values = [];
    foreach ($conditions as $condition) {
      $values[] = [
        'target_plugin_id' => $condition->getPluginId(),
        'target_plugin_configuration' => $condition->getConfiguration(),
      ];
    }
    $this->conditions->setValue($values);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate() {
    if (!$this->get('start_date')->isEmpty()) {
      // Can't use the ->date property because it resets the timezone to UTC.
      return new DrupalDateTime($this->get('start_date')->value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setStartDate(DrupalDateTime $start_date) {
    $this->get('start_date')->value = $start_date->format('Y-m-d');
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate() {
    if (!$this->get('end_date')->isEmpty()) {
      // Can't use the ->date property because it resets the timezone to UTC.
      return new DrupalDateTime($this->get('end_date')->value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setEndDate(DrupalDateTime $end_date = NULL) {
    $this->get('end_date')->value = $end_date ? $end_date->format('Y-m-d') : NULL;
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
  public function getWeight() {
    return (int) $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function available(
    PurchasableEntityInterface $entity,
    $quantity,
    Context $context
  ) {
    // The price rule should be enabled.
    if (!$this->isEnabled()) {
      return FALSE;
    }

    // Store should be one of the allowed ones.
    if (!in_array($context->getStore()->id(), $this->getStoreIds())) {
      return FALSE;
    }

    // Check start and end dates.
    $time = \Drupal::time()->getRequestTime();
    $start_date = $this->getStartDate();
    if ($start_date && $start_date->format('U') > $time) {
      return FALSE;
    }
    $end_date = $this->getEndDate();
    if ($end_date && $end_date->format('U') <= $time) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(
    PurchasableEntityInterface $entity,
    $quantity,
    Context $context
  ) {
    $conditions = $this->getConditions();

    // Price rules without conditions always apply.
    if (!$conditions) {
      return TRUE;
    }

    // Product conditions.
    $product_conditions = array_filter($conditions, function ($condition) {
      /** @var \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface $condition */
      return $condition->getEntityTypeId() == 'commerce_product';
    });
    if (!empty($product_conditions)) {
      $product_conditions = new ConditionGroup($product_conditions, 'AND');
      if (!$product_conditions->evaluate($entity->getProduct())) {
        return FALSE;
      }
    }

    // Product variation conditions.
    $product_variation_conditions = array_filter($conditions, function ($condition) {
      /** @var \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface $condition */
      return $condition->getEntityTypeId() == 'commerce_product_variation';
    });
    if (!empty($product_variation_conditions)) {
      $product_variation_conditions = new ConditionGroup($product_variation_conditions, 'AND');
      if (!$product_variation_conditions->evaluate($entity)) {
        return FALSE;
      }
    }

    // User conditions.
    $user_conditions = array_filter($conditions, function ($condition) {
      /** @var \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface $condition */
      return $condition->getEntityTypeId() == 'user';
    });
    if (!empty($user_conditions)) {
      // We need to pass a user entity to the conditions, not the AccountProxy
      // object that is stored in the context.
      $customer_id = $context->getCustomer()->id();
      if ($customer_id) {
        $customer = $this->entityManager()
          ->getStorage('user')
          ->load($customer_id);
      }
      else {
        $customer = User::getAnonymousUser();
      }

      $user_conditions = new ConditionGroup($user_conditions, 'AND');
      if (!$user_conditions->evaluate($customer)) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function calculate(
    PurchasableEntityInterface $entity,
    $quantity,
    Context $context
  ) {
    $calculation = $this->getCalculation();
    if ($calculation->getEntityTypeId() === 'commerce_product_variation') {
      return $calculation->calculate($entity, $this, $quantity, $context);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The price rule name.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('Additional information about the price rule to show to the customer'))
      ->setTranslatable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 1,
        'settings' => [
          'rows' => 3,
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['stores'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Stores'))
      ->setDescription(t('The stores for which the price rule is valid.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'commerce_store')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_entity_select',
        'weight' => 2,
      ]);

    $fields['calculation'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_price_rule_calculation')
      ->setLabel(t('Calculation'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_plugin_radios',
        'weight' => 3,
      ]);

    $fields['conditions'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_condition')
      ->setLabel(t('Conditions'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_conditions',
        'weight' => 3,
        'settings' => [
          'entity_types' => [
            'commerce_product',
            'commerce_product_variation',
            'user'
          ],
        ],
      ]);

    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start date'))
      ->setDescription(t('The date the price rule becomes valid.'))
      ->setRequired(FALSE)
      ->setSetting('datetime_type', 'date')
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 5,
      ]);

    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End date'))
      ->setDescription(t('The date after which the price rule is invalid.'))
      ->setRequired(FALSE)
      ->setSetting('datetime_type', 'date')
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 6,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether the price rule is enabled.'))
      ->setDefaultValue(TRUE)
      ->setRequired(TRUE)
      ->setSettings([
        'on_label' => t('Enabled'),
        'off_label' => t('Disabled'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 0,
      ]);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of this price rule in relation to others.'))
      ->setDefaultValue(0);

    return $fields;
  }

  /**
   * Helper callback for uasort() to sort price rules by weight and label.
   *
   * @param \Drupal\commerce_price_rule\Entity\PriceRuleInterface $a
   *   The first price rule to sort.
   * @param \Drupal\commerce_price_rule\Entity\PriceRuleInterface $b
   *   The second price rule to sort.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sort(PriceRuleInterface $a, PriceRuleInterface $b) {
    $a_weight = $a->getWeight();
    $b_weight = $b->getWeight();
    if ($a_weight == $b_weight) {
      $a_label = $a->label();
      $b_label = $b->label();
      return strnatcasecmp($a_label, $b_label);
    }
    return ($a_weight < $b_weight) ? -1 : 1;
  }

}
