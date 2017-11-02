<?php

namespace Drupal\Tests\blazy\Traits;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\blazy\BlazyManager;

/**
 * A Trait common for Blazy related service managers.
 */
trait BlazyManagerUnitTestTrait {

  /**
   * Setup the unit manager.
   */
  protected function setUpUnitServices() {
    $this->entityManager      = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $this->entityStorage      = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->entityViewBuilder  = $this->getMock('Drupal\Core\Entity\EntityViewBuilderInterface');
    $this->entityTypeMock     = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->entityFieldManager = $this->getMock('\Drupal\Core\Entity\EntityFieldManagerInterface');
    $this->entityTypeManager  = $this->getMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $this->moduleHandler      = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $this->renderer           = $this->getMock('\Drupal\Core\Render\RendererInterface');
    $this->cache              = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $this->token = $this->getMockBuilder('\Drupal\Core\Utility\Token')
      ->disableOriginalConstructor()
      ->getMock();
    $this->token->expects($this->any())
      ->method('replace')
      ->willReturnArgument(0);

    $this->configFactory = $this->getConfigFactoryStub([
      'blazy.settings' => [
        'admin_css' => TRUE,
        'responsive_image' => TRUE,
        'one_pixel' => TRUE,
        'blazy' => ['loadInvisible' => FALSE, 'offset' => 100],
      ],
    ]);
  }

  /**
   * Setup the unit manager.
   */
  protected function setUpUnitContainer() {
    $container = new ContainerBuilder();
    $container->set('entity.manager', $this->entityManager);
    $container->set('entity_field.manager', $this->entityFieldManager);
    $container->set('entity_type.manager', $this->entityTypeManager);
    $container->set('module_handler', $this->moduleHandler);
    $container->set('renderer', $this->renderer);
    $container->set('config.factory', $this->configFactory);
    $container->set('cache.default', $this->cache);
    $container->set('token', $this->token);

    \Drupal::setContainer($container);

    $this->blazyManager = new BlazyManager(
      $this->entityTypeManager,
      $this->moduleHandler,
      $this->renderer,
      $this->configFactory,
      $this->cache
    );
  }

  /**
   * Prepare image styles.
   */
  protected function setUpImageStyle() {
    $styles = [];

    $dummies = ['blazy_crop', 'large', 'medium', 'small'];
    foreach ($dummies as $key => $style) {
      $mock = $this->getMock('Drupal\Core\Config\Entity\ConfigEntityInterface');
      $mock->expects($this->any())
        ->method('getCacheTags')
        ->willReturn([]);

      $styles[$style] = $mock;
    }

    $ids = array_keys($styles);
    $storage = $this->getMock('\Drupal\Core\Config\Entity\ConfigEntityStorageInterface');
    $storage->expects($this->any())
      ->method('loadMultiple')
      ->with($ids)
      ->willReturn($styles);

    $style = 'large';
    $storage->expects($this->any())
      ->method('load')
      ->with($style)
      ->will($this->returnValue($styles[$style]));

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('image_style')
      ->willReturn($storage);

    return $styles;
  }

  /**
   * Prepare Responsive image styles.
   */
  protected function setUpResponsiveImageStyle() {
    $styles = $image_styles = [];
    foreach (['fallback', 'small', 'medium', 'large'] as $style) {
      $mock = $this->getMock('Drupal\Core\Config\Entity\ConfigEntityInterface');
      $mock->expects($this->any())
        ->method('getConfigDependencyName')
        ->willReturn('image.style.' . $style);
      $mock->expects($this->any())
        ->method('getCacheTags')
        ->willReturn([]);

      $image_styles[$style] = $mock;
    }

    foreach (['blazy_picture_test', 'blazy_responsive_test'] as $style) {
      $mock = $this->getMock('Drupal\responsive_image\ResponsiveImageStyleInterface');
      $mock->expects($this->any())
        ->method('getImageStyleIds')
        ->willReturn(array_keys($image_styles));
      $mock->expects($this->any())
        ->method('getCacheTags')
        ->willReturn([]);

      $styles[$style] = $mock;
    }

    $ids = array_keys($styles);
    $storage = $this->getMock('\Drupal\Core\Config\Entity\ConfigEntityStorageInterface');
    $storage->expects($this->any())
      ->method('loadMultiple')
      ->with($ids)
      ->willReturn($styles);

    $style = 'blazy_picture_test';
    $storage->expects($this->any())
      ->method('load')
      ->with($style)
      ->willReturn($styles[$style]);

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('responsive_image_style')
      ->willReturn($storage);
    $this->entityTypeManager->expects($this->any())
      ->method('getEntityTypeFromClass')
      ->with('Drupal\image\Entity\ImageStyle')
      ->willReturn('image_style');

    return $styles;
  }

}
