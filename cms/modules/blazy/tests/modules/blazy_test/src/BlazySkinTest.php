<?php

namespace Drupal\blazy_test;

/**
 * Implements BlazySkinTestInterface.
 */
class BlazySkinTest implements BlazySkinTestInterface {

  /**
   * {@inheritdoc}
   */
  public function skins() {
    return [
      'default' => [
        'name' => 'Default',
        'provider' => 'blazy_test',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function features() {
    return [
      'default' => [
        'name' => 'Default',
        'provider' => 'blazy_test',
      ],
    ];
  }

}
