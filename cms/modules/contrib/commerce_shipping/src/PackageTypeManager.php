<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageType;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\physical\WeightUnit;

/**
 * Manages discovery and instantiation of package type plugins.
 *
 * @see \Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageTypeInterface
 * @see plugin_api
 */
class PackageTypeManager extends DefaultPluginManager implements PackageTypeManagerInterface {

  /**
   * Default values for each package type plugin.
   *
   * @var array
   */
  protected $defaults = [
    'id' => '',
    'remote_id' => '',
    'label' => '',
    'dimensions' => [],
    'weight' => NULL,
    // A shipping method plugin ID. Used to optionally restrict the package type
    // to shipping methods with the specified plugin.
    'shipping_method' => NULL,
    'class' => PackageType::class,
  ];

  /**
   * Constructs a new PackageTypeManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'commerce_package_type_plugins');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('commerce_package_types', $this->moduleHandler->getModuleDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    $definition['id'] = $plugin_id;
    foreach (['remote_id', 'label', 'dimensions'] as $required_property) {
      if (empty($definition[$required_property])) {
        throw new PluginException(sprintf('The package_type "%s" must define the "%s" property.', $plugin_id, $required_property));
      }
    }
    foreach (['length', 'width', 'height', 'unit'] as $dimension_property) {
      if (empty($definition['dimensions'][$dimension_property])) {
        throw new PluginException(sprintf('The package type "%s" property "dimensions" must have a "%s" key.', $plugin_id, $dimension_property));
      }
    }
    if (isset($definition['weight'])) {
      foreach (['number', 'unit'] as $weight_property) {
        if (!isset($definition['weight'][$weight_property])) {
          throw new PluginException(sprintf('The package type "%s" property "weight" must have a "%s" key.', $plugin_id, $weight_property));
        }
      }
    }
    else {
      // Package types should have a weight value even if they're weightless.
      $definition['weight'] = ['number' => 0, 'unit' => WeightUnit::GRAM];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitionsByShippingMethod($shipping_method) {
    $definitions = $this->getDefinitions();
    foreach ($definitions as $id => $definition) {
      if (!empty($definition['shipping_method']) && $definition['shipping_method'] != $shipping_method) {
        unset($definitions[$id]);
      }
    }
    uasort($definitions, function ($a, $b) {
      return strnatcasecmp($a['label'], $b['label']);
    });

    return $definitions;
  }

}
