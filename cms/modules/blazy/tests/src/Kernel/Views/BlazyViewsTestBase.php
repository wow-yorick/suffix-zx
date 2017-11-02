<?php

namespace Drupal\Tests\blazy\Kernel\Views;

use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\Tests\blazy\Traits\BlazyKernelTestTrait;

/**
 * Defines base class for Blazy Views integration.
 */
abstract class BlazyViewsTestBase extends ViewsKernelTestBase {

  use BlazyKernelTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'field',
    'file',
    'image',
    'filter',
    'link',
    'node',
    'text',
    'options',
    'entity_test',
    'views',
    'views_test_config',
    'views_test_data',
    'blazy',
    'blazy_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp($import_test_views);

    $this->setUpVariables();
    $this->setUpKernelInstall();
    $this->setUpKernelManager();
    $this->setUpRealImage();
  }

}
