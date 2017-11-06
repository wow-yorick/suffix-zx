<?php

namespace Drupal\state_machine_test\EventSubscriber;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkflowTransitionEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'entity_test.cancel.pre_transition' => 'onPreTransition',
      'entity_test.cancel.post_transition' => 'onPostTransition',
    ];
    return $events;
  }

  /**
   * Reacts to the 'entity_test.cancel.pre_transition' event.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onPreTransition(WorkflowTransitionEvent $event) {
    drupal_set_message(new TranslatableMarkup('@entity_label was @state_label at pre-transition (workflow: @workflow).', [
      '@entity_label' => $event->getEntity()->label(),
      '@state_label' => $event->getToState()->getLabel(),
      '@workflow' => $event->getWorkflow()->getId(),
    ]));
  }

  /**
   * Reacts to the 'entity_test.cancel.post_transition' event.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onPostTransition(WorkflowTransitionEvent $event) {
    drupal_set_message(new TranslatableMarkup('@entity_label was @state_label at post-transition (workflow: @workflow).', [
      '@entity_label' => $event->getEntity()->label(),
      '@state_label' => $event->getToState()->getLabel(),
      '@workflow' => $event->getWorkflow()->getId(),
    ]));
  }

}
