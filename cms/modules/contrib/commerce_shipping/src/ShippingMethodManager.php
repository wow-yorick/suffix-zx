<?php

namespace Drupal\commerce_shipping;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of shipping method plugins.
 *
 * @see \Drupal\commerce_shipping\Annotation\CommerceShippingMethod
 * @see plugin_api
 */
class ShippingMethodManager extends DefaultPluginManager {

  /**
   * Constructs a new ShippingMethodManager object.
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
    parent::__construct('Plugin/Commerce/ShippingMethod', $namespaces, $module_handler, 'Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodInterface', 'Drupal\commerce_shipping\Annotation\CommerceShippingMethod');

    $this->setCacheBackend($cache_backend, 'commerce_shipping_method_plugins');
  }

}
