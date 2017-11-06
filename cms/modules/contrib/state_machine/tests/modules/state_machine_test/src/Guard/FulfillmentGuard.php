<?php

namespace Drupal\state_machine_test\Guard;

use Drupal\state_machine\Guard\GuardInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowTransition;
use Drupal\Core\Entity\EntityInterface;

class FulfillmentGuard implements GuardInterface {

  /**
   * {@inheritdoc}
   */
  public function allowed(WorkflowTransition $transition, WorkflowInterface $workflow, EntityInterface $entity) {
    // Don't allow entities in fulfillment to be cancelled.
    if ($transition->getId() == 'cancel' && $entity->test_state->first()->value == 'fulfillment') {
      return FALSE;
    }
  }

}
