<?php

namespace Drupal\state_machine\Guard;

/**
 * Defines the interface for guard factories.
 */
interface GuardFactoryInterface {

 /**
   * Gets the instantiated guards for the given group id.
   *
   * @param string $group_id
   *   The group id.
   *
   * @return \Drupal\state_machine\Guard\GuardInterface[]
   */
  public function get($group_id);

}
