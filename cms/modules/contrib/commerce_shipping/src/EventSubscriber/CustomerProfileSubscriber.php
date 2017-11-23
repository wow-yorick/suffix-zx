<?php

namespace Drupal\commerce_shipping\EventSubscriber;

use Drupal\commerce_tax\Event\CustomerProfileEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomerProfileSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'commerce_tax.customer_profile' => ['onCustomerProfile'],
    ];
  }

  /**
   * Overrides the customer profile used for calculating tax.
   *
   * By default orders are taxed using the billing profile, but
   * shippable orders need to use the shipping profile instead.
   *
   * @param \Drupal\commerce_tax\Event\CustomerProfileEvent $event
   *   The transition event.
   */
  public function onCustomerProfile(CustomerProfileEvent $event) {
    $order_item = $event->getOrderItem();
    $order = $order_item->getOrder();
    if (!$order->hasField('shipments') || $order->get('shipments')->isEmpty()) {
      return;
    }

    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    foreach ($order->get('shipments')->referencedEntities() as $shipment) {
      foreach ($shipment->getItems() as $shipment_item) {
        // Different shipments could have different shipping profiles, so take
        // the one from the shipment that references the passed order item.
        if ($shipment_item->getOrderItemId() == $order_item->id()) {
          $event->setCustomerProfile($shipment->getShippingProfile());
          return;
        }
      }
    }
  }

}
