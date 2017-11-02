<?php

namespace Drupal\blazy_test;

/**
 * Provides an interface defining BlazyTest skins.
 *
 * The hook_hook_info() is deprecated, and no resolution by 1/16/16:
 *   #2233261: Deprecate hook_hook_info()
 *     Postponed till D9
 */
interface BlazySkinTestInterface {

  /**
   * Returns the dummy BlazyTest skins.
   *
   * @return array
   *   The array of the skins.
   */
  public function skins();

}
