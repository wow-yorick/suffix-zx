<?php

namespace Drupal\state_machine\Event;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowState;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the workflow transition event.
 */
class WorkflowTransitionEvent extends Event {

  /**
   * The "from" state.
   *
   * @var \Drupal\state_machine\Plugin\Workflow\WorkflowState
   */
  protected $fromState;

  /**
   * The "to" state.
   *
   * @var \Drupal\state_machine\Plugin\Workflow\WorkflowState
   */
  protected $toState;

  /**
   * The workflow.
   *
   * @var \Drupal\state_machine\Plugin\Workflow\WorkflowInterface
   */
  protected $workflow;

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * Constructs a new WorkflowTransitionEvent object.
   *
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowState $from_state
   *   The "from" state.
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowState $to_state
   *   The "to" state.
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowInterface $workflow
   *   The workflow.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   */
  public function __construct(WorkflowState $from_state, WorkflowState $to_state, WorkflowInterface $workflow, ContentEntityInterface $entity) {
    $this->fromState = $from_state;
    $this->toState = $to_state;
    $this->workflow = $workflow;
    $this->entity = $entity;
  }

  /**
   * Gets the "from" state.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowState
   *   The "from" state.
   */
  public function getFromState() {
    return $this->fromState;
  }

  /**
   * Gets the "to" state.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowState
   *   The "to" state.
   */
  public function getToState() {
    return $this->toState;
  }

  /**
   * Gets the workflow.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowInterface
   *   The workflow.
   */
  public function getWorkflow() {
    return $this->workflow;
  }

  /**
   * Gets the entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The entity.
   */
  public function getEntity() {
    return $this->entity;
  }

}
