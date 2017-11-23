<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\physical\Weight;
use Drupal\physical\MeasurementType;

/**
 * Provides the weight condition for shipments.
 *
 * @CommerceCondition(
 *   id = "shipment_weight",
 *   label = @Translation("Weight"),
 *   display_label = @Translation("Limit by shipment weight"),
 *   category = @Translation("Shipment"),
 *   entity_type = "commerce_shipment",
 * )
 */
class ShipmentWeight extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'operator' => '>',
      'weight' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $weight = $this->configuration['weight'];

    $form['operator'] = [
      '#type' => 'select',
      '#title' => $this->t('Operator'),
      '#options' => $this->getComparisonOperators(),
      '#default_value' => $this->configuration['operator'],
      '#required' => TRUE,
    ];
    $form['weight'] = [
      '#type' => 'physical_measurement',
      '#measurement_type' => MeasurementType::WEIGHT,
      '#title' => $this->t('Weight'),
      '#default_value' => $weight,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['operator'] = $values['operator'];
    $this->configuration['weight'] = $values['weight'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = $entity;
    $weight = $shipment->getWeight();
    if (!$weight) {
      // The conditions can't be applied until the weight is known.
      return FALSE;
    }
    $condition_unit = $this->configuration['weight']['unit'];
    /** @var \Drupal\physical\Weight $weight */
    $weight = $weight->convert($condition_unit);
    $condition_weight = new Weight($this->configuration['weight']['number'], $condition_unit);

    switch ($this->configuration['operator']) {
      case '>=':
        return $weight->greaterThanOrEqual($condition_weight);

      case '>':
        return $weight->greaterThan($condition_weight);

      case '<=':
        return $weight->lessThanOrEqual($condition_weight);

      case '<':
        return $weight->lessThan($condition_weight);

      case '==':
        return $weight->equals($condition_weight);

      default:
        throw new \InvalidArgumentException("Invalid operator {$this->configuration['operator']}");
    }
  }

}
