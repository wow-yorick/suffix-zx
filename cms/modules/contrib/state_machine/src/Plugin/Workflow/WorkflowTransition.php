<?php

namespace Drupal\state_machine\Plugin\Workflow;

/**
 * Defines the class for workflow transitions.
 */
class WorkflowTransition {

  /**
   * The transition id.
   *
   * @var string
   */
  protected $id;

  /**
   * The transition label.
   *
   * @var string
   */
  protected $label;

  /**
   * The "from" states.
   *
   * @var \Drupal\state_machine\Plugin\Workflow\WorkflowState[]
   */
  protected $fromStates;

  /**
   * The "to" state.
   *
   * @var \Drupal\state_machine\Plugin\Workflow\WorkflowState
   */
  protected $toState;

  /**
   * Constructs a new WorkflowTransition object.
   *
   * @param string $id
   *   The transition id.
   * @param string $label
   *   The transition label.
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowState[] $from_states
   *   The "from" states.
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowState $to_state
   *   The "to" state.
   */
  public function __construct($id, $label, array $from_states, WorkflowState $to_state) {
    $this->id = $id;
    $this->label = $label;
    $this->fromStates = $from_states;
    $this->toState = $to_state;
  }

  /**
   * Gets the id.
   *
   * @return string
   *   The id.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Gets the translated label.
   *
   * @return string
   *   The translated label.
   */
  public function getLabel() {
    return t($this->label, [], ['context' => 'workflow transition']);
  }

  /**
   * Gets the "from" states.
   *
   * @return \Drupal\state_machine\Plugin\Workflow\WorkflowState[]
   *   The "from" states.
   */
  public function getFromStates() {
    return $this->fromStates;
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

}
