<?php

namespace Drupal\commerce_shipping\EventSubscriber;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'commerce_order.cancel.post_transition' => ['onCancel'],
      'commerce_order.place.post_transition' => ['onPlace'],
      // @todo Remove onValidate/onFulfill once there is a Shipments admin UI.
      'commerce_order.validate.post_transition' => ['onValidate'],
      'commerce_order.fulfill.post_transition' => ['onFulfill'],
    ];
  }

  /**
   * Cancels the order's shipments when the order is canceled.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onCancel(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    if (!$this->orderHasShipments($order)) {
      return;
    }

    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    foreach ($order->get('shipments')->referencedEntities() as $shipment) {
      $transition = $shipment->getState()->getWorkflow()->getTransition('cancel');
      $shipment->getState()->applyTransition($transition);
      $shipment->save();
    }
  }

  /**
   * Finalizes the order's shipments when the order is placed.
   *
   * Only used if the workflow does not have a validation step.
   * Otherwise the same logic is handled by onValidate().
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onPlace(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    if ($event->getToState()->getId() != 'fulfillment' || !$this->orderHasShipments($order)) {
      return;
    }

    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    foreach ($order->get('shipments')->referencedEntities() as $shipment) {
      $transition = $shipment->getState()->getWorkflow()->getTransition('finalize');
      $shipment->getState()->applyTransition($transition);
      $shipment->save();
    }
  }

  /**
   * Finalizes the order's shipments when the order is validated.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onValidate(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    if (!$this->orderHasShipments($order)) {
      return;
    }

    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    foreach ($order->get('shipments')->referencedEntities() as $shipment) {
      $transition = $shipment->getState()->getWorkflow()->getTransition('finalize');
      $shipment->getState()->applyTransition($transition);
      $shipment->save();
    }
  }

  /**
   * Ships the order's shipments when the order is fulfilled.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onFulfill(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    if (!$this->orderHasShipments($order)) {
      return;
    }

    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    foreach ($order->get('shipments')->referencedEntities() as $shipment) {
      $transition = $shipment->getState()->getWorkflow()->getTransition('ship');
      $shipment->getState()->applyTransition($transition);
      $shipment->save();
    }
  }

  /**
   * Checks if the given order has shipments.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return bool
   *   TRUE if the order has shipments, FALSE otherwise.
   */
  protected function orderHasShipments(OrderInterface $order) {
    return $order->hasField('shipments') && !$order->get('shipments')->isEmpty();
  }

}
