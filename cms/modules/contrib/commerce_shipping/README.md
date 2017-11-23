Commerce Shipping
=================

Provides shipping functionality for Drupal Commerce.

## Setup

1. Install the module.

2. Edit your product variation type and enable the 'Shippable' trait

3. Edit your order type:
  - Select one of the fulfilment workflows.
  - Enable shipping and choose a shipment type.
  - Select the 'Shipping' checkout flow

## Shipping method availability

Shipping methods currently don't have conditions that would allow them to be conditionally
shown at checkout based on total weight, order total, or other factors.
Site builders can temporarily replicate this functionality by implementing
hook_commerce_shipping_methods_alter(&$shipping_methods, $shipment).

```
/**
 * Implements hook_commerce_shipping_methods_alter().
 */
function mymodule_commerce_shipping_methods_alter(&$shipping_methods, $shipment) {
  // Only offer the shipping method with the ID '1' for orders over $30.
  $amount = new \Drupal\commerce_price\Price('30', 'USD');
  /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
  if ($shipment->getOrder()->getTotalPrice()->lessThan($amount)) {
    // '1' is the ID of the shipping method that we're removing.
    unset($shipping_methods[1]);
  }
}
```

Issue: https://www.drupal.org/node/2826053
