<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the FlatRatePerItem shipping method.
 *
 * @CommerceShippingMethod(
 *   id = "flat_rate_per_item",
 *   label = @Translation("Flat rate per item"),
 * )
 */
class FlatRatePerItem extends FlatRate {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['rate_amount']['#description'] = t('Charged for each quantity of each shipment item.');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateRates(ShipmentInterface $shipment) {
    $quantity = 0;
    foreach ($shipment->getItems() as $shipment_item) {
      $quantity += $shipment_item->getQuantity();
    }
    // Rate IDs aren't used in a flat rate scenario because there's always a
    // single rate per plugin, and there's no support for purchasing rates.
    $rate_id = 0;
    $amount = $this->configuration['rate_amount'];
    $amount = new Price($amount['number'], $amount['currency_code']);
    $amount = $amount->multiply((string) $quantity);
    $rates = [];
    $rates[] = new ShippingRate($rate_id, $this->services['default'], $amount);

    return $rates;
  }

}
