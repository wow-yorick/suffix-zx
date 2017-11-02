<?php

namespace Drupal\Tests\blazy\Kernel;

use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\Tests\blazy\Traits\BlazyKernelTestTrait;

/**
 * Defines base class for the Blazy formatter tests.
 */
abstract class BlazyKernelTestBase extends FieldKernelTestBase {

  use BlazyKernelTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'user',
    'entity_test',
    'field',
    'field_ui',
    'file',
    'filter',
    'image',
    'breakpoint',
    'responsive_image',
    'node',
    'text',
    'blazy',
    'blazy_ui',
    'blazy_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpVariables();
    $this->setUpKernelInstall();
    $this->setUpKernelManager();
  }

}
