<?php

namespace Drupal\state_machine\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowTransition;

/**
 * Defines the interface for state field items.
 */
interface StateItemInterface extends FieldItemInterface {

  /**
   * Gets the workflow used by the field.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowInterface|false
   *   The workflow, or FALSE if unknown at this time.
   */
  public function getWorkflow();

  /**
   * Gets the label of the current state.
   *
   * @return string
   *   The label of the current state.
   */
  public function getLabel();

  /**
   * Gets the allowed transitions for the current state.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowTransition[]
   *   The allowed transitions.
   */
  public function getTransitions();

  /**
   * Applies the given transition, changing the current state.
   *
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowTransition $transition
   *   The transition to apply.
   */
  public function applyTransition(WorkflowTransition $transition);

  /**
   * Gets whether the current state is valid.
   *
   * Drupal separates field validation into a separate step, allowing an
   * invalid state to be set before validation is invoked. At that point
   * validation has no access to the previous value, so it can't determine
   * if the transition is allowed. Thus, the field item must track the state
   * changes internally, and answer via this method if the current state is
   * valid.
   *
   * @see \Drupal\state_machine\Plugin\Validation\Constraint\StateConstraintValidator
   *
   * @return bool
   *   TRUE if the current state is valid, FALSE otherwise.
   */
  public function isValid();

}
