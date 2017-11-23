<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\commerce\ConditionGroup;
use Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the shipping method entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_shipping_method",
 *   label = @Translation("Shipping method"),
 *   label_singular = @Translation("shipping method"),
 *   label_plural = @Translation("shipping methods"),
 *   label_count = @PluralTranslation(
 *     singular = "@count shipping method",
 *     plural = "@count shipping methods",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\commerce_shipping\ShippingMethodStorage",
 *     "access" = "Drupal\commerce\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\commerce\EntityPermissionProvider",
 *     "list_builder" = "Drupal\commerce_shipping\ShippingMethodListBuilder",
 *     "form" = {
 *       "default" = "Drupal\commerce_shipping\Form\ShippingMethodForm",
 *       "add" = "Drupal\commerce_shipping\Form\ShippingMethodForm",
 *       "edit" = "Drupal\commerce_shipping\Form\ShippingMethodForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "commerce_shipping_method",
 *   data_table = "commerce_shipping_method_field_data",
 *   admin_permission = "administer commerce_shipping_method",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "shipping_method_id",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/shipping-methods/add",
 *     "edit-form" = "/admin/commerce/config/shipping-methods/manage/{commerce_shipping_method}",
 *     "delete-form" = "/admin/commerce/config/shipping-methods/manage/{commerce_shipping_method}/delete",
 *     "collection" =  "/admin/commerce/config/shipping-methods"
 *   }
 * )
 */
class ShippingMethod extends ContentEntityBase implements ShippingMethodInterface {

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
  public function getPlugin() {
    return $this->get('plugin')->first()->getTargetInstance();
  }

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
    $this->set('conditions', []);
    foreach ($conditions as $condition) {
      if ($condition instanceof ConditionInterface) {
        $this->get('conditions')->appendItem([
          'target_plugin_id' => $condition->getPluginId(),
          'target_plugin_configuration' => $condition->getConfiguration(),
        ]);
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditionOperator() {
    return $this->get('condition_operator')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditionOperator($condition_operator) {
    $this->set('condition_operator', $condition_operator);
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
  public function applies(ShipmentInterface $shipment) {
    $conditions = $this->getConditions();
    if (!$conditions) {
      // Shipping methods without conditions always apply.
      return TRUE;
    }
    $order_conditions = array_filter($conditions, function ($condition) {
      /** @var \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface $condition */
      return $condition->getEntityTypeId() == 'commerce_order';
    });
    $shipment_conditions = array_filter($conditions, function ($condition) {
      /** @var \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface $condition */
      return $condition->getEntityTypeId() == 'commerce_shipment';
    });
    $order_conditions = new ConditionGroup($order_conditions, $this->getConditionOperator());
    $shipment_conditions = new ConditionGroup($shipment_conditions, $this->getConditionOperator());

    return $order_conditions->evaluate($shipment->getOrder()) && $shipment_conditions->evaluate($shipment);
  }

  /**
   * Helper callback for uasort() to sort shipping methods by weight and label.
   *
   * @param \Drupal\commerce_shipping\Entity\ShippingMethodInterface $a
   *   The first shipping method to sort.
   * @param \Drupal\commerce_shipping\Entity\ShippingMethodInterface $b
   *   The second shipping method to sort.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sort(ShippingMethodInterface $a, ShippingMethodInterface $b) {
    $a_weight = $a->getWeight();
    $b_weight = $b->getWeight();
    if ($a_weight == $b_weight) {
      $a_label = $a->label();
      $b_label = $b->label();
      return strnatcasecmp($a_label, $b_label);
    }
    return ($a_weight < $b_weight) ? -1 : 1;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['stores'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Stores'))
      ->setDescription(t('The stores for which the shipping method is valid.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'commerce_store')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', [
        'type' => 'commerce_entity_select',
        'weight' => 0,
      ]);

    $fields['plugin'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_shipping_method')
      ->setLabel(t('Plugin'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_plugin_radios',
        'weight' => 1,
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The shipping method name.'))
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

    $fields['conditions'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_condition')
      ->setLabel(t('Conditions'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_conditions',
        'weight' => 3,
        'settings' => [
          'entity_types' => ['commerce_order', 'commerce_shipment'],
        ],
      ]);

    $fields['condition_operator'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Condition operator'))
      ->setDescription(t('The condition operator.'))
      ->setRequired(TRUE)
      ->setSetting('allowed_values', [
        'AND' => t('All conditions must pass'),
        'OR' => t('Only one condition must pass'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue('AND');

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of this shipping method in relation to others.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'hidden',
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Enabled'))
      ->setDescription(t('Whether the shipping method is enabled.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 20,
      ]);

    return $fields;
  }

}
