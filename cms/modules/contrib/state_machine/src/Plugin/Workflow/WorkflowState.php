<?php

namespace Drupal\state_machine\Plugin\Workflow;

/**
 * Defines the class for workflow states.
 */
class WorkflowState {

  /**
   * The state id.
   *
   * @var string
   */
  protected $id;

  /**
   * The state label.
   *
   * @var string
   */
  protected $label;

  /**
   * Constructs a new WorkflowState object.
   *
   * @param string $id
   *   The state id.
   * @param string $label
   *   The state label.
   */
  public function __construct($id, $label) {
    $this->id = $id;
    $this->label = $label;
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
    return t($this->label, [], ['context' => 'workflow state']);
  }

  /**
   * Returns the string representation of the workflow state.
   *
   * @return string
  */
  public function __toString() {
    return $this->getLabel();
  }

}
