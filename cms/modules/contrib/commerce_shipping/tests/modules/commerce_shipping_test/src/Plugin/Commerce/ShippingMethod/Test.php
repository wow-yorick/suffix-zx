<?php

namespace Drupal\commerce_shipping_test\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\SupportsTrackingInterface;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\Core\Url;

/**
 * Provides the Test shipping method.
 *
 * @CommerceShippingMethod(
 *   id = "test",
 *   label = @Translation("Test"),
 * )
 */
class Test extends ShippingMethodBase implements SupportsTrackingInterface {

  /**
   * {@inheritdoc}
   */
  public function getTrackingUrl(ShipmentInterface $shipment) {
    $tracking_code = $shipment->getTrackingCode();
    if (!empty($tracking_code)) {
      return Url::fromUri('https://www.drupal.org/' . $tracking_code);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateRates(ShipmentInterface $shipment) {
    $rate_id = 0;
    $amount = new Price('0', 'USD');
    $rates = [];
    $rates[] = new ShippingRate($rate_id, $this->services['default'], $amount);

    return $rates;
  }

}
