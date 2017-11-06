<?php

namespace Drupal\state_machine\Plugin\Workflow;

use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the interface for workflows.
 */
interface WorkflowInterface {

  /**
   * Gets the workflow ID.
   *
   * @return string
   *   The workflow ID.
   */
  public function getId();

  /**
   * Gets the translated label.
   *
   * @return string
   *   The translated label.
   */
  public function getLabel();

  /**
   * Gets the workflow group.
   *
   * @return string
   *   The workflow group.
   */
  public function getGroup();

  /**
   * Gets the workflow states.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowState[]
   *   The states.
   */
  public function getStates();

  /**
   * Gets a workflow state with the given id.
   *
   * @param string $id
   *   The state id.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowState|null
   *   The requested state, or NULL if not found.
   */
  public function getState($id);

  /**
   * Gets the workflow transitions.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowTransition[]
   *   The transitions.
   */
  public function getTransitions();

  /**
   * Gets a workflow transition with the given id.
   *
   * @param string $id
   *   The transition id.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowTransition|null
   *   The requested transition, or NULL if not found.
   */
  public function getTransition($id);

  /**
   * Gets the possible workflow transitions for the given state id.
   *
   * Note that a possible transition might not be allowed (because of a guard
   * returning false).
   *
   * @param string $state_id
   *   The state id.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowTransition[]
   *   The possible transitions.
   */
  public function getPossibleTransitions($state_id);

  /**
   * Gets the allowed workflow transitions for the given state id.
   *
   * @param string $state_id
   *   The state id.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The parent entity.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowTransition[]
   *   The allowed transitions.
   */
  public function getAllowedTransitions($state_id, EntityInterface $entity);

  /**
   * Finds the workflow transition for moving between two given states.
   *
   * @param string $from_state_id
   *   The ID of the "from" state.
   * @param string $to_state_id
   *   The ID of the "to" state.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowTransition|null
   *   The transition, or NULL if not found.
   */
  public function findTransition($from_state_id, $to_state_id);

}
