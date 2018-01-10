<?php

namespace Drupal\commerce_order_number;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of order number generator plugins.
 *
 * @see \Drupal\commerce_order_number\Annotation\CommerceOrderNumberGenerator
 * @see plugin_api
 */
class OrderNumberGeneratorManager extends DefaultPluginManager {

  /**
   * Constructs a new OrderNumberGeneratorManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Commerce/OrderNumberGenerator', $namespaces, $module_handler, 'Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator\OrderNumberGeneratorInterface', 'Drupal\commerce_order_number\Annotation\CommerceOrderNumberGenerator');

    $this->alterInfo('commerce_order_number_generator_info');
    $this->setCacheBackend($cache_backend, 'commerce_order_number_generator_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    foreach (['id', 'label'] as $required_property) {
      if (empty($definition[$required_property])) {
        throw new PluginException(sprintf('The order number generator %s must define the %s property.', $plugin_id, $required_property));
      }
    }
  }

}
