<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\Condition;

use CommerceGuys\Addressing\Zone\Zone;
use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the shipping address condition for shipments.
 *
 * @CommerceCondition(
 *   id = "shipment_address",
 *   label = @Translation("Shipping address"),
 *   display_label = @Translation("Limit by shipping address"),
 *   category = @Translation("Customer"),
 *   entity_type = "commerce_shipment",
 * )
 */
class ShipmentAddress extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'zone' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['zone'] = [
      '#type' => 'address_zone',
      '#default_value' => $this->configuration['zone'],
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
    // Work around an Address bug where the Remove button value is kept in the
    // array.
    foreach ($values['zone']['territories'] as &$territory) {
      unset($territory['remove']);
    }
    // Don't store the label, it's always hidden and empty.
    unset($values['zone']['label']);

    $this->configuration['zone'] = $values['zone'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = $entity;
    $shipping_profile = $shipment->getShippingProfile();
    if (!$shipping_profile) {
      // The conditions can't be applied until the shipping address is known.
      return FALSE;
    }
    $zone = new Zone([
      'id' => 'shipping',
      'label' => 'N/A',
    ] + $this->configuration['zone']);
    $address = $shipping_profile->get('address')->first();

    return $zone->match($address);
  }

}
