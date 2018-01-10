<?php

namespace Drupal\commerce_order_number\EventSubscriber;

use Drupal\commerce_order_number\OrderNumberGenerationServiceInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber, that acts on the place transition of commerce order
 * entities, in order to generate and set an order number.
 */
class OrderNumberSubscriber implements EventSubscriberInterface {

  /**
   * The order number generation service.
   *
   * @var \Drupal\commerce_order_number\OrderNumberGenerationServiceInterface
   */
  protected $orderNumberGenerationService;

  /**
   * Constructs a new OrderNumberSubscriber object.
   *
   * @param \Drupal\commerce_order_number\OrderNumberGenerationServiceInterface $order_number_generation_service
   *   The order number generation service.
   */
  public function __construct(OrderNumberGenerationServiceInterface $order_number_generation_service) {
    $this->orderNumberGenerationService = $order_number_generation_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.place.pre_transition' => ['setOrderNumber'],
    ];
    return $events;
  }

  /**
   * Sets the order number on placing the order.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function setOrderNumber(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    $this->orderNumberGenerationService->generateAndSetOrderNumber($order);
  }

}
