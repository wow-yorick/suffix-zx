<?php

namespace Drupal\commerce_customization\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Commerce customization plugin manager.
 */
class CommerceCustomizationManager extends DefaultPluginManager {

  /**
   * Constructs a new CommerceCustomizationManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/CommerceCustomization', $namespaces, $module_handler, 'Drupal\commerce_customization\Plugin\CommerceCustomizationInterface', 'Drupal\commerce_customization\Annotation\CommerceCustomization');

    $this->alterInfo('commerce_customization_commerce_customization_info');
    $this->setCacheBackend($cache_backend, 'commerce_customization_commerce_customization_plugins');
  }

}
